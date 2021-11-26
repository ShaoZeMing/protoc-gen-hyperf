# protoc-gen-hyperf

This is a mirror for [google/protobuf](https://github.com/google/protobuf).

And features you may like:
- small package, only contain php source code
- use unicode for json_encode
- export compiler plugin, so you can write your plugin in PHP

Please see patches from [this](https://github.com/shaozeming/protobuf-php/compare/b8824d322bb9ef504506d0bb0ea24095295a1dd6...19b3b02b6c36b12550237986c33ac078b03f73b0).

## 项目目的
根据proto文件，生成hyperf框架gRPC client、和gRPC route, 实现业务快速开发，减少不必要的重复工作，还有利于提高代码的规范化。

## 使用方式
当前项目已经打包了可执行文件`build/protoc-gen-hyperf.phar` 可直接使用。
```bash

# 1. 将已生成的build目录中protoc-gen-hyperf.phar 文件移动到path路径中可执行目录

cp build/protoc-gen-hyperf.phar /usr/local/bin/protoc-gen-hyperf

# 2. 增加移动后文件可执行权限

chmod a+x /usr/local/bin/protoc-gen-hyperf

# 3. 在protoc命令中增加`--hyperf_out=xxx`指向生成文件目录,和php_out参数目录相同

protoc --php_out=./sdk   --hyperf_out=./sdk    helloworld.proto

# 4. 插件传参(修改clinet基础类):
# 由于插件的参数是通过--xxx_out参数中传递的key_value类型,以逗号分割,最后一个是生成目录
protoc --php_out=./sdk/   --hyperf_out=baseStub=\xxx\CoreClient:./sdk    helloworld.proto
```

## 注意事项
- 使用该插件，你的PHP需要安装protobuf 扩展
- 当前插件已支持最新3.19.1 版本的google官方代码相关功能， 后续会根据情况进行更新
