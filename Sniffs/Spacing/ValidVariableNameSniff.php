<?php

    // @codingStandardsIgnoreStart
    if (class_exists('PHP_CodeSniffer_Standards_AbstractVariableSniff', true) === false) {
        throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractVariableSniff not found');
    }

    class php_standard_Sniffs_Spacing_ValidVariableNameSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
    {
        private $_ignore = array(
            T_WHITESPACE,
            T_COMMENT,
        );

        protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
        {
            $tokens  = $phpcsFile->getTokens();
            $varName = ltrim($tokens[$stackPtr]['content'], '$');
            $phpReservedVars = array(
                '_SERVER',
                '_GET',
                '_POST',
                '_REQUEST',
                '_SESSION',
                '_ENV',
                '_COOKIE',
                '_FILES',
                'GLOBALS',
                'http_response_header',
                'HTTP_RAW_POST_DATA',
                'php_errormsg',
            );

            // If it's a php reserved var, then its ok.
            if (in_array($varName, $phpReservedVars) === true) {
                return;
            }

            $objOperator = $phpcsFile->findNext(array(T_WHITESPACE), ($stackPtr + 1), null, true);
            
            if ($tokens[$objOperator]['code'] === T_OBJECT_OPERATOR) {

                // Check to see if we are using a variable from an object.
                $var = $phpcsFile->findNext(array(T_WHITESPACE), ($objOperator + 1), null, true);

                if ($tokens[$var]['code'] === T_STRING) {

                    // Either a var name or a function call, so check for bracket.
                    $bracket = $phpcsFile->findNext(array(T_WHITESPACE), ($var + 1), null, true);

                    if ($tokens[$bracket]['code'] !== T_OPEN_PARENTHESIS) {
                        $objVarName = $tokens[$var]['content'];

                        // There is no way for us to know if the var is public or private,
                        // so we have to ignore a leading underscore if there is one and just
                        // check the main part of the variable name.
                        $originalVarName = $objVarName;

                        if (substr($objVarName, 0, 1) === '_') {
                            $objVarName = substr($objVarName, 1);
                        }

                        if (preg_match('|\d|', $objVarName) === 1) {
                            // $warning = 'Variable "%s" contains numbers but this is discouraged';
                            // $data    = array($originalVarName);
                            // $phpcsFile->addWarning($warning, $stackPtr, 'ContainsNumbers', $data);
                        }
                    }
                }
            }

            // There is no way for us to know if the var is public or private,
            // so we have to ignore a leading underscore if there is one and just
            // check the main part of the variable name.
            $originalVarName = $varName;

            if (substr($varName, 0, 1) === '_') {
                $objOperator = $phpcsFile->findPrevious(array(T_WHITESPACE), ($stackPtr - 1), null, true);
                
                if ($tokens[$objOperator]['code'] === T_DOUBLE_COLON) {
                    // The variable lives within a class, and is referenced like
                    // this: MyClass::$_variable, so we don't know its scope.
                    $inClass = true;
                }
                else {
                    $inClass = $phpcsFile->hasCondition($stackPtr, array(T_CLASS, T_INTERFACE, T_TRAIT));
                }

                if ($inClass === true) {
                    $varName = substr($varName, 1);
                }
            }

            if (PHP_CodeSniffer::isCamelCaps($varName, false, true, false) === false) {
                $error = 'Variable "%s" is not in valid camel caps format';
                $data  = array($originalVarName);
                $phpcsFile->addError($error, $stackPtr, 'NotCamelCaps', $data);
            }
            else if (preg_match('|\d|', $varName) === 1) {
                // $warning = 'Variable "%s" contains numbers but this is discouraged';
                // $data    = array($originalVarName);
                // $phpcsFile->addWarning($warning, $stackPtr, 'ContainsNumbers', $data);
            }
        }

        protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
        {
            $tokens      = $phpcsFile->getTokens();
            $varName     = ltrim($tokens[$stackPtr]['content'], '$');
            $memberProps = $phpcsFile->getMemberProperties($stackPtr);
            $public      = ($memberProps['scope'] === 'public');

            if ($public === true) {
                if (substr($varName, 0, 1) === '_') {
                    $error = 'Public member variable "%s" must not contain a leading underscore';
                    $data  = array($varName);
                    $phpcsFile->addError($error, $stackPtr, 'PublicHasUnderscore', $data);
                    return;
                }
            }

            if (PHP_CodeSniffer::isCamelCaps($varName, false, true, false) === false) {
                $error = 'Member variable "%s" is not in valid camel caps format';
                $data  = array($varName);
                $phpcsFile->addError($error, $stackPtr, 'MemberVarNotCamelCaps', $data);
            }
            else if (preg_match('|\d|', $varName) === 1) {
                // $warning = 'Member variable "%s" contains numbers but this is discouraged';
                // $data    = array($varName);
                // $phpcsFile->addWarning($warning, $stackPtr, 'MemberVarContainsNumbers', $data);
            }
        }

        protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
        {
            $tokens = $phpcsFile->getTokens();
            $phpReservedVars = array(
                '_SERVER',
                '_GET',
                '_POST',
                '_REQUEST',
                '_SESSION',
                '_ENV',
                '_COOKIE',
                '_FILES',
                'GLOBALS',
                'http_response_header',
                'HTTP_RAW_POST_DATA',
                'php_errormsg',
            );

            if (preg_match_all('|[^\\\]\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)|', $tokens[$stackPtr]['content'], $matches) !== 0) {
                foreach ($matches[1] as $varName) {
                    // If it's a php reserved var, then its ok.
                    if (in_array($varName, $phpReservedVars) === true) {
                        continue;
                    }
                    if (PHP_CodeSniffer::isCamelCaps($varName, false, true, false) === false) {
                        $error = 'Variable "%s" is not in valid camel caps format';
                        $data  = array($varName);
                        $phpcsFile->addError($error, $stackPtr, 'StringVarNotCamelCaps', $data);
                    } else if (preg_match('|\d|', $varName) === 1) {
                        // $warning = 'Variable "%s" contains numbers but this is discouraged';
                        // $data    = array($varName);
                        // $phpcsFile->addWarning($warning, $stackPtr, 'StringVarContainsNumbers', $data);
                    }
                }
            }
        }
    }
