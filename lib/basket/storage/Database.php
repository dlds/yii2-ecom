<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 31.01.14
 */

namespace opus\ecom\basket\storage;

use opus\ecom\basket\StorageInterface;
use opus\ecom\Basket;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\db\Connection;
use yii\db\Query;
use yii\web\User;

/**
 * Database-adapter for basket data storage. Assumes the existence of a table similar to:
 *
 * CREATE TABLE `eco_basket` (
 *    `session_id` varchar(255) NOT NULL,
 *    `basket_data` blob NOT NULL,
 *    PRIMARY KEY (`session_id`)) ENGINE=InnoDB;
 *
 * If userComponent is set, it tries to call getId() from the component and use the result as user identifier. If it
 * fails, or if $userComponent is not set, it will use session_id as user identifier
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\ecom\basket\storage
 */
class Database extends Object implements StorageInterface
{
    /**
     * @var string Name of the user component
     */
    public $userComponent;
    /**
     * @var string Name of the database component
     */
    public $dbComponent = 'db';

    /**
     * @var string Name of the basket table
     */
    public $table;

    /**
     * @var string Name of the
     */
    public $idField = 'session_id';

    /**
     * @var string Name of the field holding serialized session data
     */
    public $dataField = 'basket_data';

    /**
     * @var bool If set to true, empty basket entries will be deleted
     */
    public $deleteIfEmpty = false;

    /**
     * @var Connection
     */
    protected $db;
    /**
     * @var User
     */
    protected $user;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->db = \Yii::$app->get($this->dbComponent);

        if (isset($this->userComponent)) {
            $this->user = \Yii::$app->get($this->userComponent);
        }

        if (!isset($this->table)) {
            throw new InvalidConfigException('Please specify "table" in basket configuration');
        }
    }

    /**
     * @param Basket $basket
     * @return mixed
     */
    public function load(Basket $basket)
    {
        $identifier = $this->getIdentifier($basket->getSession()->getId());

        $query = new Query();
        $query->select($this->dataField)
            ->from($this->table)
            ->where([$this->idField => $identifier]);

        $items = [];

        if ($data = $query->createCommand($this->db)->queryScalar()) {
            $items = unserialize($data);
        }

        return $items;
    }

    /**
     * @param string $default
     * @return string
     */
    protected function getIdentifier($default)
    {
        $id = $default;
        if ($this->user instanceof User) {
            $id = $this->user->getId();
        }
        return $id;
    }

    /**
     * @param \opus\ecom\Basket $basket
     * @return void
     */
    public function save(Basket $basket)
    {
        $identifier = $this->getIdentifier($basket->getSession()->getId());

        $items = $basket->getItems();
        $sessionData = serialize($items);

        $command = $this->db->createCommand();

        if (empty($items) && true === $this->deleteIfEmpty) {
            $command->delete($this->table, [$this->idField => $identifier]);
        } else {
            $command->setSql("
                REPLACE {{{$this->table}}}
                SET
                    {{{$this->dataField}}} = :val,
                    {{{$this->idField}}} = :id
            ")->bindValues([
                    ':id' => $identifier,
                    ':val' => $sessionData,
                ]);
        }
        $command->execute();
    }
}
