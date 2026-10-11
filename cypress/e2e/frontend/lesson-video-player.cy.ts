import { type PlayerEventMap } from '@Core/ts/types/player';

/**
 * Lesson video player (Video.js engine) end-to-end coverage.
 *
 * Expects the configured `single_course_slug` course to have a self-hosted HTML5
 * lesson (no YouTube/Vimeo) so no third-party network is required. Strict Mode,
 * resume and auto-next assertions only run when the seeded lesson enables them
 * (read from `#tutor_video_tracking_information`).
 */

interface TrackingData {
  strict_mode?: boolean;
  best_watch_time?: number;
  autoload_next_course_content?: boolean;
}

const baseUrl = Cypress.env('base_url') as string;
const courseSlug = Cypress.env('single_course_slug') as string;

function readTracking(win: Window): TrackingData {
  const input = win.document.getElementById('tutor_video_tracking_information') as HTMLInputElement | null;

  if (!input?.value) {
    return {};
  }

  try {
    return JSON.parse(input.value) as TrackingData;
  } catch {
    return {};
  }
}

function openLessonInLearningArea(): void {
  cy.intercept('POST', `${baseUrl}/wp-admin/admin-ajax.php`).as('ajaxRequest');
  cy.visit(`${baseUrl}/courses/${courseSlug}/`);

  cy.get('body').then(($body) => {
    if ($body.find('#tutor-login-form').length) {
      cy.getByInputName('log').type(Cypress.env('student_username'));
      cy.getByInputName('pwd').type(Cypress.env('student_password'));
      cy.get('#tutor-login-form button').contains('Sign In').click();
    }
  });

  cy.url().should('include', courseSlug);

  cy.get('body').then(($body) => {
    if ($body.text().includes('Enroll Now')) {
      cy.get('button').contains('Enroll Now').click();
    }
  });

  cy.handleCourseStart();

  cy.get('.tutor-course-topic-item a').first().click();
  cy.url().should('include', '/lesson');
  cy.get('tutor-video-player', { timeout: 20000 }).should('exist');
}

describe('Tutor lesson video player (Video.js engine)', () => {
  beforeEach(() => {
    openLessonInLearningArea();
  });

  it('mounts the engine for an HTML5 lesson and drops Plyr', () => {
    cy.get('.plyr__video-embed').should('not.exist');

    cy.window().then((win) => {
      expect((win as unknown as { Plyr?: unknown }).Plyr, 'window.Plyr').to.equal(undefined);
    });

    cy.get('tutor-video-player').should('exist').find('video').should('exist');

    cy.window().then((win) => {
      const player = win.TutorCore.player.getActive();
      expect(player, 'active player').to.not.equal(null);
      expect(player?.provider).to.equal('html5');
    });
  });

  it('fires ready for the live handle', () => {
    cy.window().then((win) => {
      const player = win.TutorCore.player.getActive();
      expect(player, 'active player').to.not.equal(null);

      return new Cypress.Promise<void>((resolve, reject) => {
        if (!player) {
          resolve();
          return;
        }

        if (player.getDuration() > 0) {
          resolve();
          return;
        }

        const off = player.on('ready', () => {
          off();
          resolve();
        });

        setTimeout(() => {
          off();
          reject(new Error('ready did not fire'));
        }, 15000);
      });
    });
  });

  it('toggles the fullscreen state and emits fullscreenchange with the element', () => {
    cy.window().then((win) => {
      const player = win.TutorCore.player.getActive();
      expect(player, 'active player').to.not.equal(null);

      if (!player) {
        return;
      }

      const host = player.element;
      let payload: PlayerEventMap['fullscreenchange'] | null = null;
      const off = player.on('fullscreenchange', (event) => {
        payload = event;
      });

      Object.defineProperty(win.document, 'fullscreenElement', {
        configurable: true,
        get: () => host,
      });
      win.document.dispatchEvent(new win.Event('fullscreenchange'));

      cy.wrap(host).should('have.class', 'is-fullscreen');
      cy.wrap(null).then(() => {
        expect(payload?.element).to.equal(host);
        off();
      });
    });
  });

  it('clamps a forward seek in Strict Mode', () => {
    cy.window().then((win) => {
      const data = readTracking(win);
      const player = win.TutorCore.player.getActive();

      if (!data.strict_mode || !player) {
        cy.log('Lesson is not in Strict Mode; skipping clamp assertions');
        return;
      }

      const duration = player.getDuration();
      const target = Math.max(duration - 2, 5);
      player.seek(target);

      cy.wait(1000);
      cy.wrap(null).then(() => {
        expect(player.getCurrentTime(), 'strict-mode clamped time').to.be.lessThan(target);
      });
    });
  });

  it('resumes playback at the seeded best watch time', () => {
    cy.window().then((win) => {
      const data = readTracking(win);
      const player = win.TutorCore.player.getActive();

      if (!player || !data.best_watch_time || data.best_watch_time <= 0) {
        cy.log('No seeded best_watch_time; skipping resume assertions');
        return;
      }

      expect(player.getCurrentTime(), 'resumed time').to.be.closeTo(Math.floor(data.best_watch_time), 2);
    });
  });

  it('advances to the next lesson when the video ends', () => {
    cy.window().then((win) => {
      const data = readTracking(win);
      const player = win.TutorCore.player.getActive();

      if (!player || !data.autoload_next_course_content) {
        cy.log('Auto-next is disabled; skipping navigation assertions');
        return;
      }

      cy.url().then((url) => {
        const duration = player.getDuration();
        player.seek(Math.max(duration - 1, 0));
        void player.play();

        cy.url({ timeout: 15000 }).should('not.equal', url);
      });
    });
  });
});
