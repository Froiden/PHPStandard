<?php
// @codingStandardsIgnoreStart
class FroidenStandard_Sniffs_Spacing_SpaceBetweenBlocksSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * An example return value for a sniff that wants to listen for whitespace
     * and any comments would be:
     *
     * <code>
     *    return array(
     *            T_WHITESPACE,
     *            T_DOC_COMMENT,
     *            T_COMMENT,
     *           );
     * </code>
     *
     * @return int[]
     * @see    Tokens.php
     */
    public function register()
    {
        return array(
            T_IF,
            T_ELSE,
            T_ELSEIF,
            T_SWITCH,
            T_WHILE,
            T_FOR,
            T_FOREACH,
            T_CLOSE_CURLY_BRACKET
        );
    }

    /**
     * Called when one of the token types that this sniff is listening for
     * is found.
     *
     * The stackPtr variable indicates where in the stack the token was found.
     * A sniff can acquire information this token, along with all the other
     * tokens within the stack by first acquiring the token stack:
     *
     * <code>
     *    $tokens = $phpcsFile->getTokens();
     *    echo 'Encountered a '.$tokens[$stackPtr]['type'].' token';
     *    echo 'token information: ';
     *    print_r($tokens[$stackPtr]);
     * </code>
     *
     * If the sniff discovers an anomaly in the code, they can raise an error
     * by calling addError() on the PHP_CodeSniffer_File object, specifying an error
     * message and the position of the offending token:
     *
     * <code>
     *    $phpcsFile->addError('Encountered an error', $stackPtr);
     * </code>
     *
     * @param PHP_CodeSniffer_File $phpcsFile The PHP_CodeSniffer file where the
     *                                        token was found.
     * @param int $stackPtr The position in the PHP_CodeSniffer
     *                                        file's token stack where the token
     *                                        was found.
     *
     * @return void|int Optionally returns a stack pointer. The sniff will not be
     *                  called again on the current file until the returned stack
     *                  pointer is reached. Return (count($tokens) + 1) to skip
     *                  the rest of the file.
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        switch ($tokens[$stackPtr]['type']) {
            case 'T_ELSE':
            case 'T_IF':
            case 'T_ELSEIF':
            case 'T_SWITCH':
            case 'T_WHILE':
            case 'T_FOR':
            case 'T_FOREACH':
                $semicolonLine = $phpcsFile->findPrevious([T_SEMICOLON, T_OPEN_CURLY_BRACKET], $stackPtr - 1, null, false);
                $lineDiff = $tokens[$stackPtr]["line"] - $tokens[$semicolonLine]["line"];

                if ($lineDiff < 2 && $tokens[$semicolonLine]["type"] != "T_OPEN_CURLY_BRACKET") {
                    $error = 'Construct blocks should be separated by two lines';
                    $phpcsFile->addError($error, $stackPtr);
                }
                break;
            case 'T_CLOSE_CURLY_BRACKET':
                // Which condition this token belongs to
                $conditionPosition = $tokens[$stackPtr]["scope_condition"];
                $conditionType = $tokens[$conditionPosition]["type"];

                // Find next non white space token
                if ($conditionType === "T_ELSE" || $conditionType == "T_FOREACH" || $conditionType == "T_IF"
                    || $conditionType == "T_ELSE" || $conditionType == "T_WHILE"
                    || $conditionType == "T_SWITCH" || $conditionType == "T_FOR"
                )
                {
                    // Find next non-white space (start of next statement/block
                    $nonWhitePosition = $stackPtr + 1;
                    $onlyIfBlock = true;

                    while(true) {
                        if ($tokens[$nonWhitePosition]["type"] == "T_ELSEIF"
                            || $tokens[$nonWhitePosition]["type"] == "T_ELSE")  {
                            // This is not an only if block
                            $onlyIfBlock = false;
                        }

                        if ($tokens[$nonWhitePosition]["type"] == "T_WHITESPACE") {
                            $nonWhitePosition++;
                            continue;
                        }
                        else {
                            break;
                        }
                    }

                    if (!$onlyIfBlock) {
                        // If this is not only if block, then break, because we need to check this condition
                        // for else statements
                        break;
                    }

                    $lineDiff = $tokens[$nonWhitePosition]["line"] - $tokens[$stackPtr]["line"];

                    if ($lineDiff < 2 && $tokens[$nonWhitePosition]["type"] != "T_CLOSE_CURLY_BRACKET") {
                        $error = 'Construct blocks should be separated by two lines';
                        $phpcsFile->addError($error, $stackPtr);
                    }
                }
                break;
        }
    }
}
