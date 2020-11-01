<?php
function int4(int $i)
{
    return pack('I',$i);
}

Co\run(function(){


    $n=0;
    while ($n < 100000){
        \Co\System::sleep(0.05);
        $n++;
        go(function (){
            $client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
            $client->set(array(
                'open_length_check'     => true,
                'package_max_length'    => 81920,
                'package_length_type'   => 'L',
                'package_length_offset' => 0,
                'package_body_offset'   => 0,
            ));
            if (!$client->connect('39.101.214.137', 9501))
            {
                echo "connect failed. Error: {$client->errCode}\n";
                return;
            }
            $str = '=> 和->的区别，我目前自己觉得->是访问一个对象的属性和方法 =>是用于数组，明天百度 （嗯没错的）new obj() 和new obj ；在php里没有不同的意义唯一不同的是带括号的可以给构造函数传参 构造函数不能返回值 new的这个对象也不能。 要return的话可以单独写个init静态函数。对象里面的函数和变量分三种类型默认是公开的 还有私有和保护public（公有）：公有的类成员可以在任何地方被访问。protected（受保护）：受保护的类成员则可以被其自身以及其子类和父类访问。（这里的父类写是这样写，但其实老师说是错的，我也确实没理解过来代码是按逐行往下，它的父类怎么提前去访问这个函数或属性呢，然后这里子类是指这个类继承了其他的类，不是在这个类里嵌套声明的类在写的时候不能嵌套 ，只能继承！）private（私有）：私有的类成员则只能被其定义所在的类访问。empty 和isset的区别是empty是判断这个值是否为空比如null为空 un什么来着不会拼 “”都算空但isset 例如你这样赋值：$str=”;这样才算空(没有测试单纯的自己觉得，明天有空再试试吧)另外在这个对象的作用域里操作这个对象的函数和变量时必须要使用this来标识引用的这个对象的函数，变量
php变量在声明的时候不能指定类型，应该是本身是弱类型的然后操蛋的就是如果我先申明一个变量然后把一个对象赋值给这个变量，然后调用这个变量的函数，vscode没有智能提示….如果直接使用函数不用对象的形式是有提示的这点很操蛋，xdebug也不太好使网上的教程都只教你🐎，emmm我应该认真去找一套不套路的视频。
然后今天就记录这么多吧其实基本上这个作业都是一边百度一边写都能理解也肯定是bug一堆，目前对象的这些概念都能理解但还不熟记不太清，但又懒得看教程反正就几个接口 争取毕业前有几个拿的出手的作品 en，懒得整理了今天就这样记录一下玩会睡觉11.22-22点36分public 公开 当前类可以调用 子类可以访问 实例化之后可以访问protected 保护 当前类的可以被自身类访问 可以被子类访问private 私有 只能在当前类调用拍黄片的类不能在写的时候嵌套类 只能继承类的静态方法和属性 在当前内调用需要使用self::在外面需要使用::11.22-21点43命名空间 namespace用了命空间之后调用函数，类 类的变量（全局变量不用） 必须带上 命名空间不然是找不到的参考这个百度知道写的很简介https://zhidao.baidu.com/question/1446426021047408980.html自己理解命名空间的作用是防止在引入不同的类库时，出现重复命名 被后者覆盖失效namespace 必须在所有代码之前 一个php文件可以声明多个namespace 最好用花括号包括起来当然也可以不包括。
include 和require是有区别的 这个区别还没有测试，我不清楚include和include_once 的区别是 前者可以被重复引用，后者不管代码当前代码域被执行多少次 只要是同一个文件就只会执行一次（猜测没有测试）。但是有文章说尽量少使用include_one因为它影响效率会去检索一边是否已引用12.2-22点18分PHP变量作用域：在局部作用域中不能访问全局变量在全局作用域中不能访问局部变量include 只是把另外一个文件引入进来然后按编码顺序执行了一边所以 另外一个文件里声明的全局变量，规则不变在当前文件一样可以访问。
但PHP的全局变量和我以前所认知的全局变量不一样，全局变量和局部变量互不干扰也就是在函数，类中访问不到全局变量，要想访问都必须用global声明一边，例如：global $str; 不管是在类中还是在函数中。另外在类中访问类的属性一定要使用this 直接以变量的形式访问是不行的，要理解为属性，调用当前对象的属性 而且不用$符号 例如：this->str（js的理解起来就习惯点）另外PHP变量不需要使用var声明….我一直用varvar mdzz这些笔记都是随手记的感觉好乱啊PHP类里面的函数如果和类名相同的话在被继承和声明的时候会被调用但又感觉和构造函数不一样，构造函数如果被继承了声明子类其父类的构造函数不会被执行，但这个都会执行。thinkphp 类的初始化函数 _initialize在类被继承的时候也会被执行thinkphp 的公共模板的路径 是控制器路径/具体的模板文件名关键是这里如果引入出错了 tp只会报没有找到模板而且路径为空，并不好排查问题另外总结一下thinkphp路径的命名
下面的都是我直接复制的文档基本上就算不按这个命名也不会怎样但类文件一定要驼峰 首字母大写不然会报错 另外类名也要驼峰命名规范头部引用之后应该就可以直接调用，但在使用助手的情况下可以直接调用当前类的validate方法 参数就是路径可以传完整的路径 也可以不传入，不传入的话默认去查找当前模块的验证类
但如果要使用场景的话必须至少传入验证文件名（也就是验证类名）和场景名称例如：article.add 文章验证的添加场景tp5数据库部分tp5数据库模型会自动给create_time字段赋值模型的调用方法 头部use 之后可以直接调用静态方法::但好像不全只有几个常用的也可能是vscode没有提示到快捷的使用方法 使用get方法 传入主键  然后可以直接访问返回的数据 以数组的形式同时可以直接链式使用save方法 直接更新数据，添加数据的方法：new 一个数据模型 并直接传入数据 然后调用save如果需要自动过滤非数据库字段 在save之前链式调用 allowFlid(true)模型关联简单的一对一：model类创建一个函数 函数名随意但最好为另一张表的表名 小驼峰然后内部调用hasone函数并返回例如：tp5数据库部分tp5数据库模型会自动给create_time字段赋值模型的调用方法 头部use 之后可以直接调用静态方法::但好像不全只有几个常用的也可能是vscode没有提示到快捷的使用方法 使用get方法 传入主键  然后可以直接访问返回的数据 以数组的形式同时可以直接链式使用save方法 直接更新数据，添加数据的方法：new 一个数据模型 并直接传入数据 然后调用save如果需要自动过滤非数据库字段 在save之前链式调用 allowFlid(true)模型关联简单的一对一：model类创建一个函数 函数名随意但最好为另一张表的表名 小驼峰然后内部调用hasone函数并返回例如：还有一个和hasOne类似的方法但我没明白！tp5Validate部分创建和模块同名的validate 放在应用的validate文件夹内 例如：支持正则表达式好像但基本上用一下内置的规则就行了基本的有三个属性：protected $rouleprotected $messageprotected $scene调用方式tp5数据库部分tp5数据库模型会自动给create_time字段赋值模型的调用方法 头部use 之后可以直接调用静态方法::但好像不全只有几个常用的也可能是vscode没有提示到快捷的使用方法 使用get方法 传入主键  然后可以直接访问返回的数据 以数组的形式同时可以直接链式使用save方法 直接更新数据，添加数据的方法：new 一个数据模型 并直接传入数据 然后调用save如果需要自动过滤非数据库字段 在save之前链式调用 allowFlid(true)模型关联简单的一对一：model类创建一个函数 函数名随意但最好为另一张表的表名 小驼峰然后内部调用hasone函数并返回例如：tp5数据库部分tp5数据库模型会自动给create_time字段赋值模型的调用方法 头部use 之后可以直接调用静态方法::但好像不全只有几个常用的也可能是vscode没有提示到快捷的使用方法 使用get方法 传入主键  然后可以直接访问返回的数据 以数组的形式同时可以直接链式使用save方法 直接更新数据，添加数据的方法：new 一个数据模型 并直接传入数据 然后调用save如果需要自动过滤非数据库字段 在save之前链式调用 allowFlid(true)模型关联简单的一对一：model类创建一个函数 函数名随意但最好为另一张表的表名 小驼峰然后内部调用hasone函数并返回例如：还有一个和hasOne类似的方法但我没明白！tp5Validate部分创建和模块同名的validate 放在应用的validate文件夹内 例如：支持正则表达式好像但基本上用一下内置的规则就行了基本的有三个属性：protected $rouleprotected $messageprotected $scene调用方式生生不息，繁荣昌盛';
            $str = [];
            $str[] = '单10';
            $str[] = '双50';
            $str[] = '万23456千23456除各1';
            $rand = 2;
            $str = $str[$rand];
            $len  = pack('i',strlen($str)+4);
            $client->send($len.$str);
            //sleep(0.5);
            //}
            $result = $client->recv();
            if ($result == false){
                echo $client->errMsg;
            }else{
                echo $result.PHP_EOL;
            }
            $client->close();
        });
    }

});