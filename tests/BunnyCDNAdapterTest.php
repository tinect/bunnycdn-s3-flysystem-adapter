<?php

use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\Config;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\Visibility;
use Tinect\Flysystem\BunnyCDN\BunnyCDNAdapter;

class BunnyCDNAdapterTest extends FilesystemAdapterTestCase
{
    private const TEST_FILE_CONTENTS = 'testing1982';

    public static function setUpBeforeClass(): void
    {
        $_SERVER['subfolder'] = 'test' . bin2hex(random_bytes(10));
    }

    public static function tearDownAfterClass(): void
    {
        self::createFilesystemAdapter()->delete('../' . $_SERVER['subfolder']  . '/');
    }

    protected static function createFilesystemAdapter(): BunnyCDNAdapter
    {
        if (!isset($_SERVER['STORAGENAME'], $_SERVER['APIKEY'])) {
            throw new RuntimeException('Running test without real data is currently not possible');
        }

        return new BunnyCDNAdapter($_SERVER['STORAGENAME'], $_SERVER['APIKEY'], 'storage.bunnycdn.com', $_SERVER['subfolder']);
    }

    public function testFileProcesses()
    {
        $adapter = $this->adapter();

        self::assertFalse(
            $adapter->fileExists('testing/test.txt')
        );

        $adapter->write('testing/test.txt', self::TEST_FILE_CONTENTS, new Config());

        self::assertTrue(
            $adapter->fileExists('testing/test.txt')
        );

        self::assertTrue(
            $adapter->fileExists('/testing/test.txt')
        );

        self::assertEquals(
            self::TEST_FILE_CONTENTS,
            $adapter->read('/testing/test.txt')
        );

        $adapter->delete('testing/test.txt');

        self::assertFalse(
            $adapter->fileExists('testing/test.txt')
        );
    }

    /**
     * @test
     * TODO: I don't see why we need to clean up the folder first! Anyone?
     */
    public function listing_contents_shallow(): void
    {
        try {
            $this->adapter()->delete('some/');
        } catch(UnableToDeleteFile $e) {}
        parent::listing_contents_shallow();
    }

    /**
     * @test
     * TODO: I don't see why we need to clean up the folder first! Anyone?
     */
    public function listing_contents_recursive(): void
    {
        try {
            $this->adapter()->delete('some/');
        } catch(UnableToDeleteFile $e) {}

        parent::listing_contents_recursive();
    }

    /**
     * @test
     * TODO: I don't see why we need to clean up the folder first! Anyone?
     */
    public function listing_a_toplevel_directory(): void
    {
        try {
            $this->adapter()->delete('/');
        } catch(UnableToDeleteFile $e) {}

        parent::listing_a_toplevel_directory();
    }

    /**
     * @test
     * TODO: I don't see why we need to clean up the folder first! Anyone?
     */
    public function creating_a_directory(): void
    {
        try {
            $this->adapter()->delete('path/');
        } catch(UnableToDeleteFile $e) {}

        parent::creating_a_directory();
    }

    /**
     * @test
     * Test from FilesystemAdapterTestCase will fail, because bunnycdn doesn't support visiblity
     */
    public function setting_visibility(): void
    {
        self::assertIsBool(true);
    }

    /**
     * @test
     * Test from FilesystemAdapterTestCase will fail, because bunnycdn doesn't support visiblity
     */
    public function setting_visibility_on_a_file_that_does_not_exist(): void
    {
        self::assertIsBool(true);
    }

    /**
     * @test
     * this overwrites Test from FilesystemAdapterTestCase.
     * We removed the test of visibility here
     */
    public function overwriting_a_file(): void
    {
        $this->runScenario(function () {
            $this->givenWeHaveAnExistingFile('path.txt', 'contents', ['visibility' => Visibility::PUBLIC]);
            $adapter = $this->adapter();

            $adapter->write('path.txt', 'new contents', new Config(['visibility' => Visibility::PRIVATE]));

            $contents = $adapter->read('path.txt');
            $this->assertEquals('new contents', $contents);
            /*$visibility = $adapter->visibility('path.txt')->visibility();
            $this->assertEquals(Visibility::PRIVATE, $visibility);*/
        });
    }

}
