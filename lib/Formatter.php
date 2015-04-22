<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace dlds\ecom;

/**
 * Provides formatting functionality for dlds\ecom package
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\ecom
 */
class Formatter extends \yii\i18n\Formatter
{
    /**
     * @param string $value
     * @return string
     */
    public function asPrice($value)
    {
        return \Yii::$app->formatter->asCurrency($value);
    }
}