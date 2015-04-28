Yii2 Face Recognization extension based on Baidu Tech
=====================================================
Face Recognization

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist lubaogui/yii2-face "*"
```

or add

```
"lubaogui/yii2-face": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php

use lubaogui\face\FaceRepository;

$faceRepo = new FaceRepository();
//create image array or single image, image column should be the content of the image file
$images = [
    
    'url'=> [
        'targetid'=>xxx,
        'callback'=>xxx,
        'overrite'=>0|1,
        'image'=>file_content,
    ],

];
//save images to face repository
$faceRepo->saveImages($images);


//query image 

$result = [];
$image = '@webroot/images/xxxx.jpg'; 
$faceRepo->searchByImage($image, $result);

//search result will passed to $result
