<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace dlds\shop;

/**
 * Provides formatting functionality for dlds\shop package
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\shop
 */
class Formatter extends \yii\i18n\Formatter
{
    /**
     * @param string $value
     * @return string
     */
    public function asPrice($value)
    {
        return $this->format($value / 100, 'currency');
    }
}