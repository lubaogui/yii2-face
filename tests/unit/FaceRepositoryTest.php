<?php

/*
 * This file is part of the Imagine package.
 *
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace lubaogui\face\test;

use lubaogui\face\FaceRepository;

class FaceRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * 提交给IDL的图片需要以URL形式提交，domainname是测试用的域名地址
     */
    private $domainName = "http://babyhome.baidu.com";

    private $faces = [
            '1' => $this->domainName . '/images/test01.jpg',
            '2' => $this->domainName . '/images/test02.jpg',
            ];

    private $faceFiles; 
    private $faceTmpDirectory = '/tmp/faceTest/';

    private $faceRepos;

    /**
     * 构建测试用实例对象, 两个头像URL地址以及对应的文件
     */
    protected function setUp() {
        $this->faceRepos = new FaceRepository();
        @mkdir($faceTmpDirectory);
        $this->assertTrue(is_dir($faceTmpDirectory));
        foreach ($this->faces as $faceId => $faceUrl) {
            $facePath = $this->faceTmpDirectory . '/test' . $faceId . '.jpg';
            $faceContent =  file_get_contents ($faceUrl);
            file_put_contents ($faceContent, $facePath);
        }
    }

    /**
     * 测试向库中保存人脸图片
     */
    public function testSaveImages()
    {
        $saveStatus = $this->faceRepos->saveFaces($this->faces);
        $this->assertTrue($saveStatus);
    }

    /**
     * 测试向库中保存空信息
     */
    public function testSaveNullImages()
    {
        $faces = [];
        $saveStatus = $this->faceRepos->saveFaces($faces);
        $this->assertFalse($saveStatus);
    }

    /**
     * 测试向库中保存不存在的图片
     */
    public function testSaveNotExistsImages()
    {
        $faces = ['abcfdsfasxx.jpg'=>'xxxx'];
        $saveStatus = $this->faceRepos->saveFaces($faces);
        $this->assertFalse($saveStatus);
    }

    /**
     * 搜索人脸照片
     */
    public function testSearchByImage()
    {
        foreach ($this->faces as $faceUrl) {
            $fileContent = file_get_contents($faceUrl);
            $searchResult = $faceRepos->searchByImage($faceFile, $result); 
            $this->assertTrue($searchResult);
            $this->assertTrue(count($result) > 0);
        }
    }

    /**
     * 搜索不存在的人脸照片
     */
    public function testSearchByImageNotExist()
    {
        $faces = ['xxxxaabac.jpg' => '111222', 'xaaab.jpg' => '11212'];
        foreach ($faces as $faceUrl) {
            $fileContent = file_get_contents($faceUrl);
            $searchResult = $faceRepos->searchByImage($faceFile, $result); 
            $this->assertFalse($searchResult);
        }
    }

    /**
     * 删除人脸图片功能
     */
    public function testDeleteImage()
    {
        $deleteStatus = $this->faceRepos->deleteByImages($this->faces); 
        $this->assertTrue($deleteStatus);
    }

    /**
     * 删除不存在的人脸图片功能
     */
    public function testDeleteNotExistImage()
    {
        $faces = ['xxxaaa' => '1111'];
        $deleteStatus = $this->faceRepos->deleteByImages($faces); 
        $this->assertFalse($deleteStatus);
    }

    /**
     * 删除测试用对象和文件 
     */
    protected function tearDown()
    {
        $dirHandle = opendir($this->faceTmpDirectory);
        while (1) {
            $file = readdir($dirHandle);
            if ($file) {
                unlink($file);
            }
            else {
                break;
            }
        }
        @rmdir($this->faceTmpDirectory);
    }
}
