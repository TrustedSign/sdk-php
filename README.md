# sdk-php
可信签php语言开发包


## 安装

```sh
composer require trustedsign/sdk-php
```

## 用法

```php
$client = new TrustedSignSDK\TrustedSignClient([
    'app_key' => '9b5062s452mdf4d6',
    'app_secret' => '2e396b27a1958279240e088c677e4721'
]);

try {
  $response = $client->get('/template');
} catch(TrustSignSDK\SDKException $e) {
  echo 'server returned an error: ' . $e->getMessage();
  exit;
}
```