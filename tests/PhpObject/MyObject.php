<?php
/**
 * Created by PhpStorm.
 * User: Administrador
 * Date: 31/01/2018
 * Time: 11:03 AM
 */

namespace Tests\PhpObject;

/**
 * Class MyObject
 */
class MyObject
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var \DateTimeInterface
     */
    private $date;
    /**
     * @var float
     */
    private $mount;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MyObject
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MyObject
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTimeInterface $date
     * @return MyObject
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return float
     */
    public function getMount()
    {
        return $this->mount;
    }

    /**
     * @param float $mount
     * @return MyObject
     */
    public function setMount($mount)
    {
        $this->mount = $mount;
        return $this;
    }
}