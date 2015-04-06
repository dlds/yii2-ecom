<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 27.01.14
 */

namespace dlds\ecom\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * Class PayAssetBundle
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package dlds\ecom\assets
 */
class PayAssetBundle extends AssetBundle
{
    public $sourcePath;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        $this->sourcePath = dirname(__FILE__);
        parent::__construct($config);
    }
}