module.exports = {
	env: {
		node: true,
		browser: true,
		es6: true,
	},
	extends: [ 'wordpress' ],
	parser: 'babel-eslint',
	parserOptions: {
		sourceType: 'module',
	},
	rules: {
		complexity: [
			'warn',
			{
				max: 4,
			},
		],
		quotes: [ 2, 'single', { avoidEscape: true } ],
		'linebreak-style': 0,
		'max-lines-per-function': [
			'error',
			{
				max: 50,
				skipBlankLines: true,
				skipComments: true,
			},
		],
		'max-depth': [ 'error', 2 ],
		camelcase: 'off',
		'comma-dangle': [ 2, 'always-multiline' ],
	},
};
