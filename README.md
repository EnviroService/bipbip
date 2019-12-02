Getting Started for Projects

Prerequisites

Check composer is installed
Check yarn & node are installed
Install

Clone this project
Run composer install
Run yarn install
Working

Run php bin/console server:run to launch your local php web server
Run yarn run dev --watch to launch your local server for assets
Testing

Run ./bin/phpcs to launch PHP code sniffer
Run ./bin/phpstan analyse src --level max to launch PHPStan
Run ./bin/phpmd src text phpmd.xml to launch PHP Mess Detector
Run ./bin/eslint assets/js to launch ESLint JS linter
Run ./bin/sass-lint -c sass-linter.yml to launch Sass-lint SASS/CSS linter
Windows Users

If you develop on Windows, you should edit you git configuration to change your end of line rules with this command :

git config --global core.autocrlf true

Deployment

Add additional notes about how to deploy this on a live system

Built With

Symfony
GrumPHP
PHP_CodeSniffer
PHPStan
PHPMD
ESLint
Sass-Lint
Travis CI
Contributing

Please read CONTRIBUTING.md for details on our code of conduct, and the process for submitting pull requests to us.

Versioning

Authors

Wild Code School trainers team

License

MIT License

Copyright (c) 2019 aurelien@wildcodeschool.fr

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Acknowledgments