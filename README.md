## PSR-7

PSR-7 implementation for PHP 7.4+

[![Latest Version](https://img.shields.io/github/release/Furious-PHP/psr7.svg?style=flat-square)](https://github.com/Furious-PHP/psr7/releases)
[![Build Status](https://scrutinizer-ci.com/g/Furious-PHP/psr7/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Furious-PHP/psr7/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/Furious-PHP/psr7/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Quality Score](https://img.shields.io/scrutinizer/g/Furious-PHP/psr7.svg?style=flat-square)](https://scrutinizer-ci.com/g/Furious-PHP/psr7)
[![Maintainability](https://api.codeclimate.com/v1/badges/71ecfc66e6100d3ffa0d/maintainability)](https://codeclimate.com/github/Furious-PHP/psr7/maintainability)
[![Total Downloads](https://poser.pugx.org/furious/psr7/downloads)](https://packagist.org/packages/furious/psr7)
[![Monthly Downloads](https://poser.pugx.org/furious/psr7/d/monthly.png)](https://packagist.org/packages/furious/psr7)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Install:

    composer require furious/psr7
    
Use:

    use Furious\Psr7\Factory\ServerRequestFactory;
    
    $request = (new ServerRequestFactory)->fromGlobals();
    $response = /* get response by request */
    
    // emitting a response
    
    $someHttpRunner->run($response);