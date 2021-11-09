# protobuf-php

This is a mirror for [google/protobuf](https://github.com/google/protobuf).

And features you may like:
- small package, only contain php source code
- use unicode for json_encode
- export compiler plugin, so you can write your plugin in PHP

Please see patches from [this](https://github.com/shaozeming/protobuf-php/compare/b8824d322bb9ef504506d0bb0ea24095295a1dd6...19b3b02b6c36b12550237986c33ac078b03f73b0).

# version
Only mirror stable tag from the offical repo.

# install

	composer require shaozeming/protobuf -v



### 项目目的
> 生成hyperf server interface/client/route register

### 打包方式
> 由于 lvht/protobuf依赖pb库与现在项目可能存在冲突，因此使用phar打包方式
>
>

### 打包项目
```bash
php build.php
```

### 使用方式
```bash
# 1. 将生成的build目录.phar文件移动到path路径中可执行目录

cp build/protoc-gen-hyperf.phar /usr/local/bin/protoc-gen-hyperf

# 2. 增加移动后文件可执行权限

chmod a+x /usr/local/bin/protoc-gen-hyperf

# 3. 在protoc命令中增加`--hyperf_out=xxx`指向生成文件目录,和php_out参数目录相同

protoc --php_out=./sdk/   --hyperf_out=./sdk    helloworld.proto

# 4. 插件传参(修改clinet基础类):
# 由于插件的参数是通过--xxx_out参数中传递的key_value类型,以逗号分割,最后一个是生成目录

protoc --php_out=./sdk/   \
--hyperf_out=baseStub=\Youyao\Framework\Client\CoreClient:./sdk    helloworld.proto

```

### prototool使用参考
```yaml
# Paths to exclude when searching for Protobuf files.
# These can either be file or directory names.
# If there is a directory name, that directory and all sub-directories will be excluded.
#excludes:
#  - path/to/a
#  - path/to/b/file.proto

# Protoc directives.
protoc:
  # The Protobuf version to use from https://github.com/protocolbuffers/protobuf/releases.
  # By default use 3.11.0.
  # You probably want to set this to make your builds completely reproducible.
  version: 3.11.0

  # Additional paths to include with -I to protoc.
  # By default, the directory of the config file is included,
  # or the current directory if there is no config file.
  includes:
    - ../common/


  # If not set, compile will fail if there are unused imports.
  # Setting this will ignore unused imports.
  allow_unused_imports: true

# Create directives.
create:
  # List of mappings from relative directory to base package.
  # This affects how packages are generated with create.
  packages:
    # This means that a file created "foo.proto" in the current directory will have package "bar".
    # A file created "a/b/foo.proto" will have package "bar.a.b".
    - directory: .
      name: proto

# Lint directives.
lint:
  # The lint group to use.
  # Available groups: "uber1", "uber2", "google", "empty".
  # The default group is the "uber1" lint group for backwards compatibility reasons,
  # however we recommend using the "uber2" lint group.
  # The special group "empty" has no linters, allowing you to manually specify all
  # lint rules in lint.rules.add.
  # Run prototool lint --list-all-lint-groups to see all available lint groups.
  # Run prototool lint --list-lint-group GROUP to list the linters in the given lint group.
  group: google

  # Linter files to ignore.
  # These can either be file or directory names.
  # If there is a directory name, that directory and all sub-directories will be ignored.
  ignores:
    - id: RPC_NAMES_CAMEL_CASE
      files:
        - .
    - id: SYNTAX_PROTO3
      files:
        - .

  # Linter rules.
  # Run prototool lint --list-all-linters to see all available linters.
  # Run prototool lint --list-linters to see the currently configured linters.
  rules:

    # The specific linters to add.
    add:
      - RPC_NAMES_CAMEL_CASE
      - SERVICE_NAMES_CAMEL_CASE
      - ENUM_NAMES_CAPITALIZED

    # The specific linters to remove.
    remove:
      - COMMENTS_NO_INLINE
      - RPC_NAMES_CAPITALIZED
      - MESSAGES_HAVE_COMMENTS
      - ENUM_NAMES_CAMEL_CASE
      - SERVICE_NAMES_CAPITALIZED

#break:
#  # Include beta packages in breaking change detection.
#  # Beta packages have the form "foo.bar.vMAJORbetaBETA" where MAJOR > 0 and BETA > 0.
#  # By default, beta packages are ignored.
#  include_beta: true
#  # Allow stable packages to depend on beta packages.
#  # By default, the breaking change detector will error if a stable package
#  # depends on a breaking package.
#  # If include_beta is true, this is implicitly set.
#  allow_beta_deps: true

# Code generation directives.
generate:
  # Options that will apply to all plugins of type go and gogo.
  go_options:
    # The base import path. This should be the go path of the prototool.yaml file.
    # This is required if you have any go plugins.
    import_path: uber/foo/bar.git/idl/uber

    # Extra modifiers to include with Mfile=package.
    extra_modifiers:
      google/api/annotations.proto: google.golang.org/genproto/googleapis/api/annotations
      google/api/http.proto: google.golang.org/genproto/googleapis/api/annotations
  # The list of plugins.
  plugins:
    # The plugin name. This will go to protoc with --name_out, so it either needs
    # to be a built-in name (like java), or a plugin name with a binary
    # protoc-gen-name.

    - name: php
#      flags: plugin=protoc-gen-Grpc=grpc_php_plugin
      output: ../../sdk

    - name: doc
      flags: markdown,questionnaire.md
      output: ../../docs/questionnaire

    - name: youyao-hyperf
      flags: baseStub=\Youyao\Framework\Client\CoreClient
      output: ../../sdk
```

> 将上述文件保存成prototool.yaml在proto文件目录下
>
> 在目录下执行prototool generate就可以生成所有proto文件配置
>
> 上述yaml中include可以用来包含其它目录, 参数实际上为protoc -I参数的内容
