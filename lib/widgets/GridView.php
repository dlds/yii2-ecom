<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 24.01.14
 */

namespace dlds\shop\widgets;

/**
 * Base Grid View for lists that deal with ecom elements. Provides default formatter
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\shop\widgets
 */
class GridView extends \yii\grid\GridView
{
    /**
     * @inheritdoc
     */
    public $formatter = ['class' => '\dlds\shop\Formatter'];
} 