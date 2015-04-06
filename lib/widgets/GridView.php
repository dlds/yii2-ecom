<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 24.01.14
 */

namespace dlds\ecom\widgets;

/**
 * Base Grid View for lists that deal with ecom elements. Provides default formatter
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\ecom\widgets
 */
class GridView extends \yii\grid\GridView
{
    /**
     * @inheritdoc
     */
    public $formatter = ['class' => '\dlds\ecom\Formatter'];
} 