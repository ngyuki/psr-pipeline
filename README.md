# PSR Pipeline

PSR-15 のミドルウェアパイプラインのディスパッチャーの実装。

zend-stratigility が delegate で SplQueue を使っているのを見て、クロージャーの入れ子で良いのでは？ と思って作ったサンプル。

下記で簡単なベンチを実行できます。

```sh
php -n benchmark/benchmark.php
```

素のミドルウェアのパイプラインならだいぶ良くなっていますが、パス指定のパイプラインが大量にあるとあまり変わらなくなります。

```
stratigility            : 2051 #/sec
my_pipeline             : 7844 #/sec
stratigility_with_path  : 1041 #/sec
my_pipeline_with_path   : 1403 #/sec
```
