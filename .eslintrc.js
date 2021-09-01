module.exports = {
    env: {
        node: true,
        browser: true,
        es6: true
    },
    extends: [
        'wordpress'
    ],
    parser: "babel-eslint",
    parserOptions: {
        'sourceType': 'module'
    },
    rules: {
        'complexity': [
            'warn',
            {
                'max': 4
            }
        ],
        "linebreak-style": 0,
        'max-lines-per-function': [
            'error',
            {
                'max': 50,
                'skipBlankLines': true,
                'skipComments': true
            }
        ],
        'max-depth': [
            'error',
            2
        ]
    }
};