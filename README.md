# PSR Pipeline

PSR-15 のミドルウェアパイプラインのディスパッチャーの実装。

zend-stratigility が delegate で SplQueue を使っているのを見て、クロージャーの入れ子で良いのでは？ と思って作ったサンプル。

下記で簡単なベンチを実行できます。

```sh
php -n benchmark/benchmark.php
```

## 2017-08-12 zend-stratigility:2.0.1

素のミドルウェアのパイプラインならだいぶ良くなっていますが、パス指定のパイプラインが大量にあるとあまり変わらなくなります。

```
stratigility            : 2051 #/sec
my_pipeline             : 7844 #/sec
stratigility_with_path  : 1041 #/sec
my_pipeline_with_path   : 1403 #/sec
```

## 2020-02-08 laminas-stratigility:3.2.1

たいして変わらなくなってる。
2.0.1 で異様に遅かったのはパス指定していなくても `/` が指定されたのと同じような動きになっていたため。
`SplQueue` は関係なかったもよう。

```
stratigility            : 13576 #/sec
my_pipeline             : 14303 #/sec
stratigility_with_path  : 1899 #/sec
my_pipeline_with_path   : 1907 #/sec
```
