<?php

namespace Natue\Bundle\InvoiceBundle\Util\Filters;

use Natue\Bundle\InvoiceBundle\Util\Contracts\Filter;

/**
 * Class RemoveSpecialCharsFilter
 * @package Natue\Bundle\InvoiceBundle\Util\filters
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class RemoveSpecialCharsFilter implements Filter
{
    /**
     * @var array
     */
    protected $mapping = [
        '/À|Á|Â|Ã|Ä|Å|Æ/' => 'A',
        '/à|á|â|ã|ä|å|æ/' => 'a',
        '/Þ/'             => 'B',
        '/þ/'             => 'b',
        '/Ć|Č|Ç/'         => 'C',
        '/ç|č|ć/'         => 'c',
        '/Đ/'             => 'D',
        '/đ/'             => 'd',
        '/È|É|Ê|Ë/'       => 'E',
        '/è|é|ê|ë/'       => 'e',
        '/Ì|Í|Î|Ï/'       => 'I',
        '/ì|í|î|ï/'       => 'i',
        '/Ñ/'             => 'N',
        '/ñ/'             => 'n',
        '/Ò|Ó|Ô|Õ|Ö|Ø/'   => 'O',
        '/ð|ò|ó|ô|õ|ö|ø/' => 'o',
        '/Ŕ/'             => 'R',
        '/ŕ/'             => 'r',
        '/Š/'             => 'S',
        '/š/'             => 's',
        '/ß/'             => 'Ss',
        '/Ù|Ú|Û|Ü/'       => 'U',
        '/ù|ú|û/'         => 'u',
        '/Ž/'             => 'Z',
        '/ž/'             => 'z',
        '/Ý/'             => 'Y',
        '/ý|ÿ/'           => 'y',
    ];

    /**
     * @param string $input
     * @return string
     */
    public function filter($input)
    {
        return preg_replace(array_keys($this->mapping), array_values($this->mapping), $input);
    }
}
