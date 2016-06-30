# PHP Coding Standard
This repository contains rulesets for **PHP CodeSniffer** and **PHP Mess Detector** along with some custom rules. These standards are followed internally at *Froiden* (www.froiden.com)

## Installation
Install this repository globally using composer:

	composer global require froiden/php_standard

Always use the latest version of the repository.

## Setup
The project will be installed in global composer folder on your PC. 
* **Windows & Mac**: `.composer/vendor` folder in your home directory
* **Ubuntu**: `.config/composer/vendor` folder in your home directory

You follow the steps respective to your favourite IDE to configure PHP CodeSniffer and PHP MessDetector and point to the two rulesets in the installation folder.
* **ruleset.xml** - For CodeSniffer
* **rulesetmd.xml** - For MessDetector

## Attribution
Some sniffs in this repository have been derived from original PHP_CodeSniffer project. Credits for those go to the respective developers.