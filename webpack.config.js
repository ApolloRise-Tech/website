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
      index: path.resolve(__dirname, 'src', 'pages', 'index', 'index.js'),
      docsdnaApp: path.resolve(__dirname, 'src', 'pages', 'cases', 'docsdnaApp', 'cases.js'),
      career: path.resolve(__dirname, 'src', 'pages', 'career', 'career.js'),
      founders: path.resolve(__dirname, 'src', 'pages', 'founders', 'founders.js'),
      portfolio: path.resolve(__dirname, 'src', 'pages', 'portfolio', 'portfolio.js'),
      successCareer: path.resolve(__dirname, 'src', 'pages', 'successCareer', 'successCareer.js'),
      successContact: path.resolve(__dirname, 'src', 'pages', 'successContact', 'successContact.js'),
      notFound: path.resolve(__dirname, 'src', 'pages', 'notFound', '404.js'),
      privacyPolicy: path.resolve(__dirname, 'src', 'pages', 'privacyPolicy', 'privacyPolicy.js'),
      parseon: path.resolve(__dirname, 'src', 'pages', 'cases', 'parseon', 'cases.js'),
      parseonWebsite: path.resolve(__dirname, 'src', 'pages', 'cases', 'parseonWebsite', 'cases.js'),
      pulsar: path.resolve(__dirname, 'src', 'pages', 'cases', 'pulsar', 'cases.js'),
      pulsarWebsite: path.resolve(__dirname, 'src', 'pages', 'cases', 'pulsarWebsite', 'cases.js'),
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
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'founders', 'founders.pug'),
        filename: 'founders.html',
        chunks: ['founders'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'portfolio', 'portfolio.pug'),
        filename: 'portfolio.html',
        chunks: ['portfolio'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'privacyPolicy', 'privacyPolicy.pug'),
        filename: 'privacy-policy.html',
        chunks: ['privacyPolicy'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'cases', 'parseon', 'cases.pug'),
        filename: 'cases/parseon.html',
        chunks: ['parseon'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'cases', 'parseonWebsite', 'cases.pug'),
        filename: 'cases/parseon-website.html',
        chunks: ['parseonWebsite'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'cases', 'pulsar', 'cases.pug'),
        filename: 'cases/pulsar.html',
        chunks: ['pulsar'],
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src', 'pages', 'cases', 'pulsarWebsite', 'cases.pug'),
        filename: 'cases/pulsar-website.html',
        chunks: ['pulsarWebsite'],
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
          {
            from: path.resolve(__dirname, 'src', 'public', 'robots.txt'),
            to: 'robots.txt',
          },
          {
            from: path.resolve(__dirname, 'src', 'public', 'sitemap.xml'),
            to: 'sitemap.xml',
          },
          {
            from: path.resolve(__dirname, 'src', 'public', '.htaccess'),
            to: '.htaccess',
          },
          {
            from: path.resolve(__dirname, 'src', 'sources', 'images', 'linkImage.png'),
            to: 'sources/images/linkImage.png',
          },
          {
            from: path.resolve(__dirname, 'src', 'blog'),
            to: 'blog',
          },
          {
            from: path.resolve(__dirname, 'src', 'blog-admin'),
            to: 'blog-admin',
          },
          {
            from: path.resolve(__dirname, 'src', 'blog-data'),
            to: 'blog-data',
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
          test: /\.(png|jpe?g|gif|ico|webp)$/i,
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

