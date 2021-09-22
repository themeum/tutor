const { mergeWith } = require('docz-utils')
const fs = require('fs-extra')

let custom = {}
const hasGatsbyConfig = fs.existsSync('./gatsby-config.custom.js')

if (hasGatsbyConfig) {
  try {
    custom = require('./gatsby-config.custom')
  } catch (err) {
    console.error(
      `Failed to load your gatsby-config.js file : `,
      JSON.stringify(err),
    )
  }
}

const config = {
  pathPrefix: '/',

  siteMetadata: {
    title: 'Tutor',
    description:
      "<img src='.github/tutor-github.png' alt='TutorLMS' width='100%'>",
  },
  plugins: [
    {
      resolve: 'gatsby-theme-docz',
      options: {
        themeConfig: {},
        src: './',
        gatsbyRoot: null,
        themesDir: 'src',
        mdxExtensions: ['.md', '.mdx'],
        docgenConfig: {},
        menu: [],
        mdPlugins: [],
        hastPlugins: [],
        ignore: [],
        typescript: false,
        ts: false,
        propsParser: true,
        'props-parser': true,
        debug: false,
        native: false,
        openBrowser: null,
        o: null,
        open: null,
        'open-browser': null,
        root: '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/.docz',
        base: '/',
        source: './',
        'gatsby-root': null,
        files: '**/*.{md,markdown,mdx}',
        public: '/v2-library/bundle',
        dest: '.docz/dist',
        d: '.docz/dist',
        editBranch: 'master',
        eb: 'master',
        'edit-branch': 'master',
        config: '',
        title: 'Tutor',
        description:
          "<img src='.github/tutor-github.png' alt='TutorLMS' width='100%'>",
        host: 'localhost',
        port: 3000,
        p: 3000,
        separator: '-',
        paths: {
          root: '/Users/hasan/Sites/tutor/wp-content/plugins/tutor',
          templates:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/node_modules/docz-core/dist/templates',
          docz: '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/.docz',
          cache:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/.docz/.cache',
          app: '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/.docz/app',
          appPackageJson:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/package.json',
          appTsConfig:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/tsconfig.json',
          gatsbyConfig:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/gatsby-config.js',
          gatsbyBrowser:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/gatsby-browser.js',
          gatsbyNode:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/gatsby-node.js',
          gatsbySSR:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/gatsby-ssr.js',
          importsJs:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/.docz/app/imports.js',
          rootJs:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/.docz/app/root.jsx',
          indexJs:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/.docz/app/index.jsx',
          indexHtml:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/.docz/app/index.html',
          db:
            '/Users/hasan/Sites/tutor/wp-content/plugins/tutor/.docz/app/db.json',
        },
      },
    },
  ],
}

const merge = mergeWith((objValue, srcValue) => {
  if (Array.isArray(objValue)) {
    return objValue.concat(srcValue)
  }
})

module.exports = merge(config, custom)
