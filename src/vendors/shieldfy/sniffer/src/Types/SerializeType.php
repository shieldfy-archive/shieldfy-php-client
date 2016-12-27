<?php

namespace Shieldfy\Sniffer\Types;

use Shieldfy\Sniffer\TypeInterface;

class SerializeType implements TypeInterface
{
    public function sniff($input)
    {
        return $this->isSerialize($input);
    }

    /**
     * @author		Chris Smith <code+php@chris.cs278.org>
     * @copyright	Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
     * @license		http://sam.zoy.org/wtfpl/ WTFPL
     *
     * @param string $value Value to test for serialized form
     *
     * @return bool True if $value is serialized data, otherwise false
     */
    private function isSerialize($value)
    {
        if (!is_string($value)) {
            return false;
        }
        if ($value === 'b:0;') {
            return true;
        }
        $length = strlen($value);
        $end = '';
        switch ($value[0]) {
            case 's':
                if ($value[$length - 2] !== '"') {
                    return false;
                }
            case 'b':
            case 'i':
            case 'd':
                // This looks odd but it is quicker than isset()ing
                $end .= ';';
            case 'a':
            case 'O':
                $end .= '}';
                if ($value[1] !== ':') {
                    return false;
                }
                switch ($value[2]) {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                        break;
                    default:
                        return false;
                }
            case 'N':
                $end .= ';';
                if ($value[$length - 1] !== $end[0]) {
                    return false;
                }
                break;
            default:
                return false;
        }
        if (@unserialize($value) === false) {
            return false;
        }

        return true;
    }

    public function __toString()
    {
        return 'serialize';
    }
}
