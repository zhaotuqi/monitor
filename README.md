```
PHP 代码中通过调用如下函数打点：

1. 打点计数：
    Monitor\Client::inc("a_test_key");
    
2. 打点耗时：

    $start = round(microtime(true) * 1000);
    // ... 业务代码逻辑
    
    $end = round(microtime(true) * 1000);
    
    Monitor\Client::cost("xxx_cost", $end - $start); // 耗时是以毫秒计算
    
3. 打点指定值（打什么报什么）：
    Monitor\Client::set("a_test_key", 111);
