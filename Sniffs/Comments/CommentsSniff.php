<?php

    // @codingStandardsIgnoreStart
    class php_standard_Sniffs_Comments_CommentsSniff implements PHP_CodeSniffer_Sniff
    {

        public function register() {
            return [T_COMMENT];
        }

        public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {

            $tokens = $phpcsFile->getTokens();

            if ($tokens[$stackPtr]["type"] == "T_COMMENT") {
                $content = $tokens[$stackPtr]["content"];
                // This is a comment. If it is not a region comment, then it should have a space
                // at start and must begin if a capital letter if not a multi line comment

                // Check region comment
                if (!(strpos($content, "//region") === 0) && !(strpos($content, "//endregion") === 0)) {
                    // Check multiline comment
                    $multiline = false;

                    if (isset($tokens[$stackPtr - 1])) {
                        $prevToken = $phpcsFile->findPrevious(T_COMMENT, $stackPtr - 1, null, false);

                        if ($tokens[$stackPtr]["line"] - $prevToken["line"] == 1) {
                            $multiline = true;
                        }
                    }

                    if ($multiline) {
                        $regEx = "/\/\/ [A-Z0-9][^.]*/";
                    }
                    else {
                        $regEx = "/\/\/ [a-zA-Z0-9][^.]*/";
                    }

                    if (preg_match($regEx, $content) === 0) {
                        $error = 'Line comments must start with a capital letter';
                        $phpcsFile->addError($error, $stackPtr);
                    }
                }
            }
        
        }
    }