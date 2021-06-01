<?php

namespace App\Doctrine\Query\PostgreSQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Position extends FunctionNode
{
    private $substring;
    private $stringPrimary;

    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf(
            'POSITION(%s IN %s)',
            $sqlWalker->walkArithmeticPrimary($this->substring),
            $sqlWalker->walkArithmeticPrimary($this->stringPrimary)
        );
    }

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->substring = $parser->StringExpression();

        $parser->match(Lexer::T_IN);

        $this->stringPrimary = $parser->StringPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
