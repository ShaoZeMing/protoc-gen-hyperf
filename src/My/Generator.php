<?php
namespace My;


use Google\Protobuf\Field;
use Google\Protobuf\GPBEmpty;
use Google\Protobuf\Internal\CodeGeneratorRequest;
use Google\Protobuf\Internal\CodeGeneratorResponse_File as File;
use Google\Protobuf\Internal\FileDescriptor;
use Google\Protobuf\Internal\FileDescriptorProto;
use Google\Protobuf\Internal\GPBWire;
use Google\Protobuf\Internal\MethodDescriptorProto as MethodDescriptor;
use Google\Protobuf\Internal\ServiceDescriptorProto;
use Google\Protobuf\Internal\ServiceDescriptorProto as ServiceDescriptor;
use Google\Protobuf\Internal\SourceCodeInfo_Location;

class Generator
{
    /**
     * @var CodeGeneratorRequest
     */
    private $request;

    private  $indent = 0;

    private $comments;

    private $namespace;

    private $interfacePrefix = "Interface";

    private $registerPrefix = "Register";

    private $clietStubPrefix = "Client";

    private $clientTrait = "ClientTrait";

    private $baseNamespace = '';

    private $baseStub = '\Hyperf\GrpcClient\BaseClient';


    public function __construct(CodeGeneratorRequest $generatorRequest)
    {
        $this->request = $generatorRequest;
        $this->parseParameter();
    }


    private function parseParameter()
    {
        $parameter_str = $this->request->getParameter();
        foreach (explode(':', $parameter_str) as $p) {
            $parts = explode('=', $p);
            if (count($parts) == 2) {
                $name = trim($parts[0]);
                $value = trim($parts[1]);

                $this->$name = $value;
            }
        }
    }

    public function generate(): array
    {
        $all_files = [];
        /** @var FileDescriptorProto $file */
         foreach ($this->request->getProtoFile() as $file) {
             $this->comments = $this->extractComments($file);
             $this->setNameSpace($file);
             /** @var ServiceDescriptorProto $service */
             foreach ($file->getService() as $index => $service) {
                 $files = $this->generateFilesForService($file, $service, $index);
                 $all_files = array_merge($all_files, $files);
             }

         }
        return $all_files;
    }

    private function setNameSpace(FileDescriptorProto $file)
    {
        $phpNameSpaceOption = $file->getOptions()->getPhpNamespace();
        if ($phpNameSpaceOption) {  //有全局空间，直接使用
            $this->namespace = $phpNameSpaceOption;
            return;
        }

        $this->namespace = str_replace('.', "\\", ucwords($file->getPackage(), '.'));
    }

    private function generateFile($nameSpace, $content, $serviceName): File
    {
        $path =$this->getFilePath($nameSpace, $serviceName);
        $file = new File();
        $file->setName($path);
        $file->setContent($content);
        return $file;
    }



    private function generateFilesForService(FileDescriptorProto $file, ServiceDescriptorProto $service, $index): array
    {

        //生成server interface
        $interfaceFiles = $this->generateServerInterface($file, $service, $index);
        //生成registry
        $registryFiles = $this->generateRouterRegistryFunction($file, $service, $index);
        //生成client stub
        $clientFiles = $this->generateClientStub($file, $service, $index);

        return [$interfaceFiles, $registryFiles, $clientFiles];
    }


    private function generateServerInterface(FileDescriptorProto $file, ServiceDescriptorProto $service, $serviceIndex): File
    {
        $proto_path = $file->getName();
        $service_name = $this->getServiceName($service);
        $ucServiceName = ucfirst($service_name);
        $interfaceName = $ucServiceName. $this->interfacePrefix;

        $p = [$this, 'e']; $in = [$this, 'in']; $out = [$this, 'out'];
        $serviceCommentLines = $this->generateComment("6,$serviceIndex");

        ob_start();
        $this->generateHeader($proto_path, $this->namespace);

        if ($serviceCommentLines) {
            $p("/**");
            foreach ($serviceCommentLines as $line) {
                $p(" * $line");
            }
            $p(" */");
        }
        $p("interface $interfaceName");
        $p("{");
        $in();
        /** @var MethodDescriptor $method */
        foreach ($service->getMethod() as $methodIndex => $method) {
            $method_name = $method->getName();

            //处理类型前面的命名空间
            $input_type = $this->packageToNamespace($method->getInputType());
//            $input_type = $method->getInputType();
            $output_type = $this->packageToNamespace($method->getOutputType());
//            $output_type = $method->getOutputType();

            $commentLines = $this->generateComment("6,$serviceIndex,2,$methodIndex");

            $p("/**");
            if ($commentLines) {
                foreach ($commentLines as $line) {
                    $p(" * $line");
                }
            }
            $p(" *");
            $p(" * @param $input_type \$request");
            $p(" * @return $output_type");
            $p(" */");
            $p("public function $method_name($input_type \$request): $output_type;");
        }
        $out();
        $p("}");
        $content = ob_get_clean();


        return $this->generateFile($this->namespace,  $content, $ucServiceName . $this->interfacePrefix);
    }

    private function generateRouterRegistryFunction(FileDescriptorProto $file, ServiceDescriptorProto $service, $serviceIndex): File
    {
        $package = $file->getPackage();
        $proto_path = $file->getName();
        $service_name = $this->getServiceName($service);
        $ucServiceName = ucwords($service_name);
        $registryName = $ucServiceName . $this->registerPrefix;

        $p = [$this, 'e']; $in = [$this, 'in']; $out = [$this, 'out'];
        $serviceCommentLines = $this->generateComment("6,$serviceIndex");

        ob_start();
        $this->generateHeader($proto_path, $this->namespace);

        if ($serviceCommentLines) {
            $p("/**");
            foreach ($serviceCommentLines as $line) {
                $p(" * $line");
            }
            $p(" */");
        }
        $p("use Hyperf\HttpServer\Router\Router;");
        $p("class {$ucServiceName}{$this->registerPrefix}");
        $p("{");
        $in();
        $p("public static function register(\$handlerFullClass)");
        $p("{");
        $in();

        $p("Router::addServer('grpc', function () use(\$handlerFullClass) {");
        $in();
        $p("Router::addGroup('/{$package}.{$service_name}', function () use(\$handlerFullClass) {");
        $in();
        /** @var MethodDescriptor $method */
        foreach ($service->getMethod() as $methodIndex => $method) {
            $method_name = $method->getName();

            $commentLines = $this->generateComment("6,$serviceIndex,2,$methodIndex");
            if ($commentLines) {
                $p("/**");
                foreach ($commentLines as $line) {
                    $p(" * $line");
                }
                $p(" */");
            }
            $p("Router::post('/{$method_name}', [\"\$handlerFullClass\", '{$method_name}']);");
        }
        $out();
        $p("});");
        $out();
        $p("});");
        $out();
        $p("}");
        $out();
        $p("}");
        $content = ob_get_clean();


        return $this->generateFile($this->namespace,  $content, $ucServiceName . $this->registerPrefix);
    }

    private function generateClientStub(FileDescriptorProto $file, ServiceDescriptorProto $service, $serviceIndex): File
    {

        $proto_path = $file->getName();
        $service_name = $this->getServiceName($service);
        $ucServiceName = ucwords($service_name);
        $interfaceName = $ucServiceName . $this->interfacePrefix;
        $package = $file->getPackage();

        $p = [$this, 'e']; $in = [$this, 'in']; $out = [$this, 'out'];
        $serviceCommentLines = $this->generateComment("6,$serviceIndex");

        ob_start();
        $this->generateHeader($proto_path, $this->namespace);

        if ($serviceCommentLines) {
            $p("/**");
            foreach ($serviceCommentLines as $line) {
                $p(" * $line");
            }
            $p(" */");
        }
        $p("class {$ucServiceName}{$this->clietStubPrefix} extends {$this->baseStub}  implements {$ucServiceName}Interface") ;
        $p("{");
        $trait = 'use \\'. substr($this->namespace,0,strrpos($this->namespace,'\\'))."\\".$this->clientTrait.";";
        $p($trait);
        $in();

        /** @var MethodDescriptor $method */
        foreach ($service->getMethod() as $methodIndex => $method) {
            $method_name = $method->getName();

            //处理类型前面的命名空间
            $input_type = $this->packageToNamespace($method->getInputType());
            $output_type = $this->packageToNamespace($method->getOutputType());
            $isClientStrem = $method->hasClientStreaming();
            $isServerStream = $method ->hasServerStreaming();
            $commentLines = $this->generateComment("6,$serviceIndex,2,$methodIndex");

            if ((!$isServerStream) && (!$isClientStrem)) {
                $func = $this->simpleRequest(
                    $input_type,
                    $output_type,
                    $package,
                    $service_name,
                    $method_name,
                    $commentLines
                );
            } elseif ($isServerStream && (!$isClientStrem)) {
                $func = $this->serverStream(
                    $input_type,
                    $output_type,
                    $package,
                    $service_name,
                    $method_name,
                    $commentLines
                );
            }  elseif ((!$isServerStream) && $isClientStrem) {
                $func = $this->clientStream(
                    $output_type,
                    $package,
                    $service_name,
                    $method_name,
                    $commentLines
                );
            } else {
                $func = $this->bidiStream(
                    $output_type,
                    $package,
                    $service_name,
                    $method_name,
                    $commentLines
                );
            }
            $p($func);
            $p();
        }

        $out();
        $p("}");
        $content = ob_get_clean();


        return $this->generateFile($this->namespace,  $content, $ucServiceName . $this->clietStubPrefix);
    }

    private function getPath(FileDescriptor $file, ServiceDescriptor $service)
    {
        $pacakgeName = $file->getPackage();
        $serviceName = $service->getName();
        return "/$pacakgeName.$serviceName";
    }

    private function simpleRequest($argumentClass, $replyClass, $package, $serviceName, $methodName, array $commentLines = [])
    {
        $comments = "";
        foreach ($commentLines as $line) {
            $comments .= "* $line.";
        }
        if (!$comments) {
            $comments = "* $serviceName.$methodName.";
        }
        return  <<<eof
/**
     $comments
     *
     * @param $argumentClass \$argument
     * @param array \$metadata
     * @param array \$options
     * @return $replyClass
     */
    public function $methodName($argumentClass \$argument, array \$metadata = [],array \$options = []): $replyClass
    {
        [\$reply, \$status] = \$this->_simpleRequest(
            '/$package.$serviceName/$methodName',
            \$argument,
            [$replyClass::class, 'decode']
        );
        if (!(\$status == 0 && \$reply instanceof $replyClass)) {
            throw new \Exception(\$reply, \$status);
        }
        
        return \$reply;
    }
eof;
    }

    private function clientStream($replyClass, $package, $serviceName, $methodName, array $commentLines = [])
    {
        $comments = "";
        foreach ($commentLines as $line) {
            $comments .= "* $line.";
        }
        if (!$comments) {
            $comments = "* $serviceName.$methodName.";
        }
        return  <<<eof
/**
     $comments
     *
     * @param array \$metadata
     * @param array \$options
     * @return \Hyperf\GrpcClient\ClientStreamingCall
     */
    function $methodName(array \$metadata = [],array \$options = []): \Hyperf\GrpcClient\ClientStreamingCall
    {
        \$caller = \$this->_clientStreamRequest(
            '/$package.$serviceName/$methodName',
            ['$replyClass', 'decode'],
            \$metadata,
            \$options
        );
        
        return \$caller;
    }
eof;
    }

    private function serverStream($argumentClass, $replyClass, $package, $serviceName, $methodName, array $commentLines = [])
    {
        $comments = "";
        foreach ($commentLines as $line) {
            $comments .= "* $line.";
        }
        if (!$comments) {
            $comments = "* $serviceName.$methodName.";
        }
        return  <<<eof
/**
     $comments
     *
     * @param $argumentClass \$argument
     * @param array \$metadata
     * @param array \$options
     * @return \Hyperf\GrpcClient\ServerStreamingCall
     */
    function $methodName($argumentClass \$argument, array \$metadata = [], array \$options = []): \Hyperf\GrpcClient\ServerStreamingCall
    {
        \$caller = \$this->_serverStreamRequest(
            '/$package.$serviceName/$methodName',
            ['$replyClass', 'decode'],
            \$metadata,
            \$options
        );
       
       \$caller->send(\$argument);
        
        return \$caller;
    }
eof;
    }

    private function bidiStream($replyClass, $package, $serviceName, $methodName, array $commentLines = [])
    {
        $comments = "";
        foreach ($commentLines as $line) {
            $comments .= "* $line.";
        }
        if (!$comments) {
            $comments = "* $serviceName.$methodName.";
        }
        return  <<<eof
/**
     $comments
     *
     * @param array \$metadata
     * @param array \$options
     * @return \Hyperf\GrpcClient\BidiStreamingCall
     */
    function $methodName(array \$metadata = [],array \$options = []): \Hyperf\GrpcClient\BidiStreamingCall
    {
        \$caller = \$this->_bidiRequest(
            '/$package.$serviceName/$methodName',
            ['$replyClass', 'decode'],
            \$metadata,
            \$options
        );
       
        return \$caller;
    }
eof;
    }


    /**
     * @param FileDescriptorProto $file
     * @return SourceCodeInfo_Location[]
     */
    private function extractComments(FileDescriptorProto $file) : array
    {
        $comments = [];

        $codeInfo = $file->getSourceCodeInfo();
        $locations = $codeInfo->getLocation();

        foreach ($locations as $location) {
            if (!$location->hasLeadingComments()) {
                continue;
            }
            $paths = iterator_to_array($location->getPath());
            $comments[implode(',', $paths)] = $location;
        }

        return $comments;
    }


    private function getFilePath($namespace, $service_name, $ext = ".php")
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);

        return $path.DIRECTORY_SEPARATOR.$service_name.$ext;
    }

    private function getServiceName(ServiceDescriptor $service): string
    {
        return $service->getName();
    }


    /**
     * @param $filePackageName
     * @param $package
     * @return string|string[]
     */
     private function packageToNamespace($package)
    {
        //TODO: 先特殊处理吧，想到办法再解决
        if ($package == '.google.protobuf.Empty') {
            return '\Google\Protobuf\GPBEmpty';
        }

        //这个没有反射处理，需要
//        $fullName = str_replace('.', "\\", ucwords($package, '.'));
        $fullName = ltrim(ucwords(strrchr($package, '.')),'.');
        if (!$this->baseNamespace) {
            return $fullName;
        } else {
            return \sprintf('%s%s', $this->baseNamespace, $fullName);
        }
    }

    private function in()
    {
        $this->indent += 4;
    }

    private function out()
    {
        $this->indent -= 4;
    }

    private function e($line = '')
    {
        if ($line) {
            echo str_pad('', $this->indent, ' '), $line, "\n";
        } else {
            echo "\n";
        }
    }

    private function generateHeader($proto_path, $namespace)
    {
        $p = [$this, 'e']; $in = [$this, 'in']; $out = [$this, 'out'];

        $p("<?php");
        $p("// Generated by the protocol buffer compiler.  DO NOT EDIT!");
        $p("// source: $proto_path");
        $p();
        $p("declare(strict_types=1);");
        $p("namespace $namespace;");
        $p();
    }

    private function generateComment($path)
    {
        $comment = $this->comments[$path] ?? null;
        if (!$comment) {
            return [];
        }
        return explode("\n", trim($comment->getLeadingComments()));
    }

}
