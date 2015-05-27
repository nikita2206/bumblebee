<?php

namespace Bumblebee\Tests\Integration\Compiler\Transformer;

use Bumblebee\Metadata\ArrayToObjectArgumentMetadata;
use Bumblebee\Metadata\ArrayToObjectMetadata;
use Bumblebee\Metadata\ArrayToObjectSettingMetadata;

class ArrayToObjectTransformerTest extends TransformerCompilationTestCase
{

    public function testBasicCase()
    {
        $t = $this->generateTransformer("foo", [
            "foo" => new ArrayToObjectMetadata("stdClass", [], [
                new ArrayToObjectSettingMetadata("qwe", [new ArrayToObjectArgumentMetadata(null, "foo")], false)
            ])
        ]);
        $transformer = $this->getFakeTransformer();

        $result = $t(["foo" => 123], $transformer);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('qwe', $result);
        $this->assertSame(123, $result->qwe);
    }

    public function testTransformWithCustomClass()
    {
        $t = $this->generateTransformer("user", [
            "user" => new ArrayToObjectMetadata('Bumblebee\Tests\Integration\Compiler\Transformer\ArrayToObjectUser', [
                new ArrayToObjectArgumentMetadata(null, "user"),
                new ArrayToObjectArgumentMetadata(null, "email"),
                new ArrayToObjectArgumentMetadata(null, "password")
            ], [
                new ArrayToObjectSettingMetadata("details", [
                    new ArrayToObjectArgumentMetadata(null, "details", false, ""),
                ], false),
                new ArrayToObjectSettingMetadata("setInfo", [
                    new ArrayToObjectArgumentMetadata(null, "address", false),
                    new ArrayToObjectArgumentMetadata(null, "phone", false)
                ])
            ])
        ]);
        $transformer = $this->getFakeTransformer();

        /** @var ArrayToObjectUser $result */
        $result = $t(["user" => "root", "email" => "root@example.com", "password" => "123",
            "details" => "...", "address" => "742 Evergreen terrace", "phone" => "555-8707"], $transformer);

        $this->assertInstanceOf('Bumblebee\Tests\Integration\Compiler\Transformer\ArrayToObjectUser', $result);
        $this->assertSame("...", $result->details);
        $this->assertSame("742 Evergreen terrace", $result->getAddress());
        $this->assertSame("root@example.com", $result->getEmail());
        $this->assertSame("123", $result->getPassword());
        $this->assertSame("555-8707", $result->getPhone());
        $this->assertSame("root", $result->getUsername());

        $result = $t(["user" => "root", "email" => "root@example.com", "password" => "123"], $transformer);
        $this->assertInstanceOf('Bumblebee\Tests\Integration\Compiler\Transformer\ArrayToObjectUser', $result);
        $this->assertSame("", $result->details);
        $this->assertSame(null, $result->getAddress());
        $this->assertSame("root@example.com", $result->getEmail());
        $this->assertSame("123", $result->getPassword());
        $this->assertSame(null, $result->getPhone());
        $this->assertSame("root", $result->getUsername());
    }

    public function testTransformWithRecursiveSubtype()
    {
        $t = $this->generateTransformer("user", [
            "user" => new ArrayToObjectMetadata('Bumblebee\Tests\Integration\Compiler\Transformer\ArrayToObjectUser', [
                new ArrayToObjectArgumentMetadata(null, "user"),
                new ArrayToObjectArgumentMetadata(null, "email", false),
                new ArrayToObjectArgumentMetadata(null, "password", false)
            ], [
                new ArrayToObjectSettingMetadata("setParent", [
                    new ArrayToObjectArgumentMetadata("user", "parent", false, null)
                ])
            ])
        ]);
        $transformer = $this->getFakeTransformer();

        $data = ["user" => "lvl3", "parent" => ["user" => "lvl2", "parent" => ["user" => "lvl1", "parent" => ["user" => "root"]]]];
        /** @var ArrayToObjectUser $result */
        $result = $t($data, $transformer);

        $this->assertInstanceOf('Bumblebee\Tests\Integration\Compiler\Transformer\ArrayToObjectUser', $result);
        $this->assertSame("lvl3", $result->getUsername());
        $this->assertInstanceOf('Bumblebee\Tests\Integration\Compiler\Transformer\ArrayToObjectUser', $result->getParent());
        $this->assertSame("lvl2", $result->getParent()->getUsername());
        $this->assertInstanceOf('Bumblebee\Tests\Integration\Compiler\Transformer\ArrayToObjectUser', $result->getParent()->getParent());
        $this->assertSame("lvl1", $result->getParent()->getParent()->getUsername());
        $this->assertInstanceOf('Bumblebee\Tests\Integration\Compiler\Transformer\ArrayToObjectUser', $result->getParent()->getParent()->getParent());
        $this->assertSame("root", $result->getParent()->getParent()->getParent()->getUsername());
    }

}

class ArrayToObjectUser
{

    protected $username;

    protected $email;

    protected $password;

    protected $address;

    protected $phone;

    public $details;

    protected $parent;

    public function __construct($username, $email, $password)
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }

    public function setParent(ArrayToObjectUser $parent = null)
    {
        $this->parent = $parent;
    }

    /** @return ArrayToObjectUser */
    public function getParent()
    {
        return $this->parent;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setInfo($address, $phone)
    {
        $this->address = $address;
        $this->phone = $phone;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

}
