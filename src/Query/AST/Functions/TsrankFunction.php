<?php namespace Digbang\Doctrine\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * TsrankFunction ::= "TSRANK" "(" StringPrimary "," StringPrimary ")"
 */
class TsrankFunction extends FunctionNode
{
	const TSRANK = 'TSRANK';

	/**
	 * @type \Doctrine\ORM\Query\AST\Node
	 */
    public $fieldName = null;

	/**
	 * @type \Doctrine\ORM\Query\AST\Node
	 */
    public $queryString = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->fieldName = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->queryString = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return
        	'ts_rank(' . $this->fieldName->dispatch($sqlWalker) . ', ' .
            ' to_tsquery(' . $this->queryString->dispatch($sqlWalker) . '))';
    }
}
