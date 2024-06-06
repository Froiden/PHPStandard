<?php


use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class DuplicateUseSniff implements Sniff
{
    public function register()
    {
        return [T_USE];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $useStatements = [];
        $currentUse = $stackPtr;

        while ($currentUse !== false) {
            $end = $phpcsFile->findNext(T_SEMICOLON, $currentUse);
            $useStatement = $phpcsFile->getTokensAsString($currentUse, $end - $currentUse + 1);

            if (isset($useStatements[$useStatement])) {
                $phpcsFile->addFixableError(
                    'Duplicate use statement found; "%s" is already in use',
                    $currentUse,
                    'DuplicateUse',
                    [$useStatement]
                );
                
                if ($phpcsFile->fixer->enabled === true) {
                    for ($i = $currentUse; $i <= $end; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }    
                     
                    // Also remove whitespace after the semicolon (new lines).
                    while (isset($tokens[$i]) === true && $tokens[$i]['code'] === T_WHITESPACE) {
                    
                        $phpcsFile->fixer->replaceToken($i, '');
                        if (strpos($tokens[$i]['content'], $phpcsFile->eolChar) !== false) {
                            break;
                        }

                        $i++;
                    }
                    
                }
            } else {
                $useStatements[$useStatement] = true;
            }

            $currentUse = $phpcsFile->findNext(T_USE, $end);
        }
    }
}
