const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyPlugin = require('copy-webpack-plugin');
const autoprefixer = require('autoprefixer');

module.exports = (env) => {
  const mode = env.mode || 'development';
  const PORT = env.port || 3000;
  const isDev = mode === 'development';

  return {
    mode,

    entry: {
      index: path.resolve(__dirname, 'src', 'pages',  'index', 'index.js'),
      solutions: path.resolve(__dirname, 'src', 'pages',  'solutions', 'solution.js'),
      why: path.resolve(__dirname, 'src', 'pages',  'why', 'why.js'),
      docsdnaApp: path.resolve(__dirname, 'src', 'pages', 'cases', 'docsdnaApp', 'cases.js'),
      career: path.resolve(__dirname, 'src', 'pages', 'career', 'career.js'),
      successCareer: path.resolve(__dirname, 'src', 'pages', 'successCareer', 'successCareer.js'),
      successContact: path.resolve(__dirname, 'src', 'pages', 'successContact', 'successContact.js'),
      notFound: path.resolve(__dirname, 'src', 'pages', 'notFound', '404.js'),
      privacyPolicy: path.resolve(__dirname, 'src', 'pages', 'privacyPolicy', 'privacyPolicy.js'),
    },

    output: {
      filename: 'js/[name].[contenthash:8].js',
      path: path.resolve(__dirname, 'build'),
      publicPath: '/',
      clean: true,
      assetModuleFilename: path.join('sources', 'images', '[name].[contenthash][ext]'),
    },

    plugins: [
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'index', 'index.pug'),
        filename: 'index.html',
        chunks: ['index'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'solutions', 'solution.pug'),
        filename: 'solutions.html',
        chunks: ['solutions'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'privacyPolicy', 'privacyPolicy.pug'),
        filename: 'privacy-policy.html',
        chunks: ['privacyPolicy'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'why', 'why.pug'),
        filename: 'why.html',
        chunks: ['why'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'cases', 'docsdnaApp', 'cases.pug'),
        filename: 'cases/docsdna-app.html',
        chunks: ['docsdnaApp'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'career', 'career.pug'),
        filename: 'career.html',
        chunks: ['career'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'successCareer', 'successCareer.pug'),
        filename: 'successCareer.html',
        chunks: ['successCareer'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'successContact', 'successContact.pug'),
        filename: 'successContact.html',
        chunks: ['successContact'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'notFound', '404.pug'),
        filename: '404.html',
        chunks: ['notFound'],
      }),
      new MiniCssExtractPlugin({
        filename: 'css/[name].[contenthash:8].css',
        chunkFilename: 'css/[name].[contenthash:8].css',
      }),
      new CopyPlugin({
        patterns: [
          {
            from: path.resolve(__dirname, 'src', 'sources/fonts'),
            to: 'sources/fonts',
            globOptions: {
              ignore: ['*.DS_Store'],
            },
          },
        ],
      }),
    ],

    module: {
      rules: [
        {
          test: /\.js$/,
          use: 'babel-loader',
          exclude: /node_modules/,
        },
        {
          test: /\.pug$/,
          use: ['pug-loader'],
        },
        {
          test: /\.scss$/,
          use: [
            isDev ? 'style-loader' : MiniCssExtractPlugin.loader,
            'css-loader',
            {
              loader: 'postcss-loader',
              options: {
                postcssOptions: {
                  plugins: [autoprefixer],
                },
              },
            },
            'sass-loader',
          ],
        },
        {
          test: /\.css$/,
          use: ['style-loader', 'css-loader'],
        },
        {
          test: /\.(png|jpe?g|gif|ico)$/i,
          type: 'asset/resource',
          generator: {
            filename: path.join('sources', 'images', '[name].[contenthash][ext]'),
          },
        },
        {
          test: /\.svg$/,
          type: 'asset/resource',
          generator: {
            filename: path.join('sources', 'icons', '[name].[contenthash][ext]'),
          },
        },
      ],
    },


    devtool: isDev ? 'inline-source-map' : undefined,

    devServer: isDev
      ? {
          watchFiles: ['build/**/*'],
          port: PORT,
          open: true,
          historyApiFallback: true,
          hot: true,
          liveReload: true,
        }
      : undefined,
  };
};

