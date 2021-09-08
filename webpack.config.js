
const path =  require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require("terser-webpack-plugin");
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

module.exports = {
  mode: 'development',
  devtool: 'source-map',
  entry: [
    './v2-library/_src/js/main.js',
    './v2-library/_src/scss/main.scss'
  ],
  output: [{
      path: path.resolve(__dirname+'/v2-library', 'bundle'),
      filename: "[name].min.js",
  }],
  module: {
    rules: [
      {
        test: /\.(sa|sc|c)ss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'sass-loader',
        ],
      },
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].min.css'
    }),
  ],
  optimization: {
    minimize: true,
    minimizer: [
      new TerserPlugin({
        terserOptions: {},
        minify: (file) => {
          const uglifyJsOptions = {
            sourceMap: true
          };
          return require("uglify-js").minify(file, uglifyJsOptions);
        },
      }),
      new CssMinimizerPlugin()
    ],
  }
}