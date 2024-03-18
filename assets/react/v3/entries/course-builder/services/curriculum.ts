import { useQuery } from '@tanstack/react-query';

export type ID = string | number;
export interface Content {
	ID: ID;
	post_title: string;
	post_content: string;
	post_name: string | null;
}
export interface LessonVideo {
	source: 'youtube' | 'vimeo';
	source_video_id: string;
	source_youtube: string;
	source_vimeo: string;
	runtime: {
		hours: string;
		minutes: string;
		seconds: string;
	};
	poster: string;
}

export interface Lesson extends Content {
	type: 'lesson';
	course_id: ID;
	attachments: unknown[];
	thumbnail: boolean;
	video: LessonVideo[];
}
export type QuestionType = 'single_choice';
export interface QuestionSetting {
	question_type: QuestionType;
	answer_required: boolean;
	randomize_question: boolean;
	question_mark: number;
	show_question_mark: boolean;
}
export interface QuestionAnswer {
	answer_id: ID;
	answer_title: string;
	is_correct: boolean;
}
export interface QuizQuestion {
	question_id: ID;
	question_title: string;
	question_description: string;
	question_type: QuestionType;
	question_mark: number;
	question_settings: QuestionSetting;
	question_answers: QuestionAnswer[];
}

export interface Quiz extends Content {
	type: 'quiz';
	questions: QuizQuestion[];
}

export interface Assignment extends Content {
	type: 'assignment';
}

export interface ZoomLive extends Content {
	type: 'zoom';
}
export interface MeetLive extends Content {
	type: 'meet';
}

export type TopicContent = Lesson | Assignment | Quiz | ZoomLive | MeetLive;

export interface CourseTopic {
	ID: ID;
	post_title: string;
	post_content: string;
	post_name: string;
	content: TopicContent[];
}

const mockQuiz: Quiz[] = [
	{
		type: 'quiz',
		ID: 576,
		post_title: 'How to create a excel file',
		post_content: 'Lorem ipsum dolor',
		post_name: 'how-to-create-a-excel-file',
		questions: [],
	},
	{
		type: 'quiz',
		ID: 577,
		post_title: 'My first quiz',
		post_content: 'Lorem ipsum dolor',
		post_name: 'my-first-quiz',
		questions: [
			{
				question_id: '2',
				question_title: 'What is this?',
				question_description: '<p>Lorem ipsum dolor</p>',
				question_type: 'single_choice',
				question_mark: 1.0,
				question_settings: {
					question_type: 'single_choice',
					answer_required: true,
					randomize_question: true,
					question_mark: 1.0,
					show_question_mark: true,
				},
				question_answers: [
					{
						answer_id: '7',
						answer_title: 'True',
						is_correct: false,
					},
					{
						answer_id: '8',
						answer_title: 'False',
						is_correct: false,
					},
					{
						answer_id: '9',
						answer_title: 'Option A',
						is_correct: false,
					},
					{
						answer_id: '10',
						answer_title: 'Option B',
						is_correct: false,
					},
					{
						answer_id: '11',
						answer_title: 'Option C',
						is_correct: true,
					},
					{
						answer_id: '12',
						answer_title: 'Option D',
						is_correct: false,
					},
				],
			},
		],
	},
];
const mockLesson: Lesson[] = [
	{
		type: 'lesson',
		ID: 151,
		post_title: 'How to Make Your Spreadsheets Look Professional',
		post_content: '',
		post_name: null,
		course_id: 148,
		attachments: [],
		thumbnail: false,
		video: [
			{
				source: 'youtube',
				source_video_id: '',
				source_youtube: 'https://www.youtube.com/watch?v=yGDwk4z9EEg',
				source_vimeo: '',
				runtime: {
					hours: '00',
					minutes: '02',
					seconds: '20',
				},
				poster: '',
			},
		],
	},
	{
		type: 'lesson',
		ID: 150,
		post_title: 'Excel Made Easy: A Beginner&#8217;s Guide to Excel Spreadsheets',
		post_content: '',
		post_name: null,
		course_id: 148,
		attachments: [],
		thumbnail: false,
		video: [
			{
				source: 'youtube',
				source_video_id: '',
				source_youtube: 'https://www.youtube.com/watch?v=yGDwk4z9EEg',
				source_vimeo: '',
				runtime: {
					hours: '00',
					minutes: '01',
					seconds: '10',
				},
				poster: '',
			},
		],
	},
	{
		type: 'lesson',
		ID: 149,
		post_title: 'Microsoft Excel: The World&#8217;s #1 Office Software',
		post_content: '',
		post_name: null,
		course_id: 148,
		attachments: [],
		thumbnail: false,
		video: [
			{
				source: 'youtube',
				source_video_id: '',
				source_youtube: 'https://www.youtube.com/watch?v=yGDwk4z9EEg',
				source_vimeo: '',
				runtime: {
					hours: '00',
					minutes: '01',
					seconds: '00',
				},
				poster: '',
			},
		],
	},
];
const mockAssignment: Assignment[] = [
	{
		type: 'assignment',
		ID: 248,
		post_title: 'Assignment 1',
		post_content: '',
		post_name: null,
	},
	{
		type: 'assignment',
		ID: 249,
		post_title: 'Assignment 2',
		post_content: '',
		post_name: null,
	},
	{
		type: 'assignment',
		ID: 250,
		post_title: 'Assignment 3',
		post_content: '',
		post_name: null,
	},
];
const mockZoomLive: ZoomLive[] = [
	{
		type: 'zoom',
		ID: 348,
		post_title: 'Zoom live session 1',
		post_content: '',
		post_name: null,
	},
	{
		type: 'zoom',
		ID: 349,
		post_title: 'Zoom Live session 2',
		post_content: '',
		post_name: null,
	},
];
const mockMeetLive: MeetLive[] = [
	{
		type: 'meet',
		ID: 448,
		post_title: 'Meet live session 1',
		post_content: '',
		post_name: null,
	},
	{
		type: 'meet',
		ID: 449,
		post_title: 'Meet live session 2',
		post_content: '',
		post_name: null,
	},
];

const mockCurriculum: CourseTopic[] = [
	{
		ID: 342,
		post_title: 'Meal Planning Basics',
		post_content:
			'The versatility of the tools and its compatibility with other software means that AutoCAD is the most used software in architectural and industrial projects. In this Domestika Basics of 5 courses, learn how to draw any type of project from scratch, alongside Alicia Sanz, model maker and interior designer.',
		post_name: 'meal-planning-basics',
		content: [...mockLesson.slice(0, 2), mockQuiz[0], mockAssignment[0], mockZoomLive[0], mockMeetLive[0]],
	},
	{
		ID: 346,
		post_title: 'Setting Up Your Diet',
		post_content:
			'The versatility of the tools and its compatibility with other software means that AutoCAD is the most used software in architectural and industrial projects. In this Domestika Basics of 5 courses, learn how to draw any type of project from scratch, alongside Alicia Sanz, model maker and interior designer.',
		post_name: 'setting-up-your-diet',
		content: [...mockLesson.slice(2, 3), mockQuiz[1], mockAssignment[1]],
	},
	{
		ID: 350,
		post_title: 'Adjusting Your Diet For Weigh Loss & Muscle Gains',
		post_content:
			'The versatility of the tools and its compatibility with other software means that AutoCAD is the most used software in architectural and industrial projects. In this Domestika Basics of 5 courses, learn how to draw any type of project from scratch, alongside Alicia Sanz, model maker and interior designer.',
		post_name: 'adjusting-your-diet-for-weigh-loss-muscle-gains',
		content: [...mockLesson.slice(2, 3), mockQuiz[1], mockAssignment[2]],
	},
	{
		ID: 354,
		post_title: 'Common Dieting Trends Explained',
		post_content: '',
		post_name: 'common-dieting-trends-explained',
		content: [],
	},
	{
		ID: 358,
		post_title: 'Dieting Tips & Strategies',
		post_content: '',
		post_name: 'dieting-tips-strategies',
		content: [...mockLesson.slice(2, 3), mockQuiz[1]],
	},
];
const getCourseCurriculum = (courseId: number) => {
	console.log({ courseId });
	return Promise.resolve({
		data: mockCurriculum,
	});
};

export const useCourseCurriculumQuery = (courseId: number) => {
	return useQuery({
		queryKey: ['CourseCurriculum', courseId],
		queryFn: () => getCourseCurriculum(courseId).then((res) => res.data),
		enabled: !!courseId,
	});
};
