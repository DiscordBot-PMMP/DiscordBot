<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

declare(strict_types=1);

use JaxkDev\DiscordBot\Models\Server;
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Plugin\Storage;
use PHPUnit\Framework\TestCase;

//TODO Add model constructor dependencies where applicable.
//TODO Better model usage.
final class StorageTest extends TestCase{

    /**
     * //TODO Depends on Server Creation.
     */
    public function testAddServer(): void{
        $s = new Server("554059221847638040", "Test Server", "UK", "282819886198030336", false, 10);
        Storage::addServer($s);
        $this->assertCount(1, Storage::getServers(), "Failed to add server to storage.");
        //Duplicated.
        Storage::addServer($s);
        $this->assertCount(1, Storage::getServers(), "Failed to detect duplicated server being added to storage.");
    }

    /**
     * //TODO Depends on Server Creation & Server::setRegion
     * @depends testAddServer
     */
    public function testUpdateServer(): void{
        $s = new Server("554059221847638040", "Test Server", "UK", "282819886198030336", false, 10);
        $s->setRegion("US");
        Storage::updateServer($s);
        $this->assertCount(1, Storage::getServers());
        //Different ID, should be added not updated.
        $s = new Server("554059221847638041", "Test Server 2", "UK", "282819886198030336", false, 20);
        Storage::updateServer($s);
        $this->assertCount(2, Storage::getServers());
    }

    /**
     * @depends testAddServer
     */
    public function testGetServers(): void{
        $data = Storage::getServers();
        $this->assertIsArray($data);
        $this->assertContainsOnlyInstancesOf(Server::class, $data);
    }

    /**
     * @depends testGetServers
     * @depends testUpdateServer
     */
    public function testGetServer(): void{
        $this->assertInstanceOf(Server::class, Storage::getServer("554059221847638040"));
        $this->assertInstanceOf(Server::class, Storage::getServer("554059221847638041"));
        $this->assertNull(Storage::getServer("111111111111111111"));
        $this->assertNull(Storage::getServer("Invalid ID"));
    }

    public function testAddUser(): void{
        $u = new User("282819886198030336", "Username", "1234", "https://discord.com/");
        Storage::addUser($u);
        $this->assertCount(1, Storage::getUsers());
    }

    /**
     * @depends testAddUser
     */
    public function testGetUser(): User{
        $user = Storage::getUser("282819886198030336");
        $this->assertInstanceOf(User::class, $user);
        $this->assertNull(Storage::getUser("282819886198030337"));
        return $user;
    }

    /**
     * @depends testGetUser
     */
    public function testGetUsers(): void{
        $data = Storage::getUsers();
        $this->assertIsArray($data);
        $this->assertContainsOnlyInstancesOf(User::class, $data);
    }

    /**
     * @depends testGetUser
     */
    public function testUpdateUser(User $user): void{
        $count = count(Storage::getUsers());
        $user->setUsername("Test Username 2");
        Storage::updateUser($user);
        $this->assertCount($count, Storage::getUsers());
    }

    /**
     * @depends testGetUser
     */
    public function testRemoveUser(User $user): void{
        $count = count(Storage::getUsers());
        Storage::removeUser($user->getId());
        $this->assertCount($count - 1, Storage::getUsers());
    }

    public function testGetTimestamp(): void{
        $this->assertGreaterThanOrEqual(0, Storage::getTimestamp());
    }

    public function testSetTimestamp(): void{
        Storage::setTimestamp(0);
        Storage::setTimestamp(time());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Timestamp must be greater than or equal to 0.");
        Storage::setTimestamp(-1);
    }

    public function testSerializeStorage(): void{
        $data = Storage::serializeStorage();
        $this->assertIsString($data);
        //TODO, Could check via un-serializing the data.
    }

    public function testEmptyGetBotUser(): void{
        $this->assertNull(Storage::getBotUser());
    }

    /**
     * @depends testEmptyGetBotUser
     */
    public function testEmptyGetBotMemberByServer(): void{
        $this->assertNull(Storage::getBotMemberByServer(""));
    }

    public function testSetBotUser(): void{
        $u = new User("282819886198030336", "Username", "1234", "https://discord.com/");
        Storage::setBotUser($u);
        //TODO Actual assertions (next Non-BC Release)
        //Shouldn't have this assertion:
        $this->assertInstanceOf(User::class, Storage::getBotUser()); //Hack for code-coverage.
    }

    /**
     * @depends testSetBotUser
     */
    public function testGetBotUser(): void{
        $this->assertInstanceOf(User::class, Storage::getBotUser());
    }

    /**
     * @depends testGetBotUser
     */
    public function testGetBotMembersByServer(): void{
        $this->assertNull(Storage::getBotMemberByServer(""));
        //TODO Check if this returns member when member exists.
    }
}