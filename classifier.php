<?php

require_once("C:\wamp\www\\vendor\autoload.php");

use Camspiers\StatisticalClassifier\Classifier\ComplementNaiveBayes;
use Camspiers\StatisticalClassifier\Model\CachedModel;
use Camspiers\StatisticalClassifier\DataSource\DataArray;

$source = new DataArray();

$source->addDocument('IT', 'This is an IT document');
$source->addDocument('Medical', 'This is a medical document');
$source->addDocument('Business', 'This is a business document');
$source->addDocument('Mili-Gov', 'This is a military or government document');

$model = new CachedModel(
    'mycachename',
    new CacheCache\Cache(
        new CacheCache\Backends\File(
            array(
                'dir' => __DIR__
            )
        )
    )
);

?>