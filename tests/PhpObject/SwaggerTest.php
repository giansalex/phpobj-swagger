<?php
/**
 * Created by PhpStorm.
 * User: Administrador
 * Date: 31/01/2018
 * Time: 10:51 AM
 */

namespace Tests\Giansalex\PhpObject;

use Giansalex\PhpObject\Swagger;
use Tests\PhpObject\MyObject;

class SwaggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Swagger
     */
    private $swagger;

    protected function setUp()
    {
        $this->swagger = new Swagger();
    }

    public function testFromObject()
    {
        $result = $this->swagger->fromClass(MyObject::class);

        $this->assertTrue(isset($result['definitions']));
        $this->assertTrue(isset($result['definitions']['MyObject']));
        $this->assertTrue(isset($result['definitions']['MyObject']['properties']));

        $props = $result['definitions']['MyObject']['properties'];
        $this->assertTrue(isset($props['id']));
        $this->assertTrue(isset($props['name']));
        $this->assertTrue(isset($props['date']));
        $this->assertTrue(isset($props['mount']));
    }

}