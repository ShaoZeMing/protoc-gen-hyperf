<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: plugin.proto

namespace Google\Protobuf\Compiler;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The plugin writes an encoded CodeGeneratorResponse to stdout.
 *
 * Generated from protobuf message <code>google.protobuf.compiler.CodeGeneratorResponse</code>
 */
class CodeGeneratorResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Error message.  If non-empty, code generation failed.  The plugin process
     * should exit with status code zero even if it reports an error in this way.
     * This should be used to indicate errors in .proto files which prevent the
     * code generator from generating correct code.  Errors which indicate a
     * problem in protoc itself -- such as the input CodeGeneratorRequest being
     * unparseable -- should be reported by writing a message to stderr and
     * exiting with a non-zero status code.
     *
     * Generated from protobuf field <code>optional string error = 1;</code>
     */
    protected $error = null;
    /**
     * A bitmask of supported features that the code generator supports.
     * This is a bitwise "or" of values from the Feature enum.
     *
     * Generated from protobuf field <code>optional uint64 supported_features = 2;</code>
     */
    protected $supported_features = null;
    /**
     * Generated from protobuf field <code>repeated .google.protobuf.compiler.CodeGeneratorResponse.File file = 15;</code>
     */
    private $file;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $error
     *           Error message.  If non-empty, code generation failed.  The plugin process
     *           should exit with status code zero even if it reports an error in this way.
     *           This should be used to indicate errors in .proto files which prevent the
     *           code generator from generating correct code.  Errors which indicate a
     *           problem in protoc itself -- such as the input CodeGeneratorRequest being
     *           unparseable -- should be reported by writing a message to stderr and
     *           exiting with a non-zero status code.
     *     @type int|string $supported_features
     *           A bitmask of supported features that the code generator supports.
     *           This is a bitwise "or" of values from the Feature enum.
     *     @type \Google\Protobuf\Compiler\CodeGeneratorResponse\File[]|\Google\Protobuf\Internal\RepeatedField $file
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Plugin::initOnce();
        parent::__construct($data);
    }

    /**
     * Error message.  If non-empty, code generation failed.  The plugin process
     * should exit with status code zero even if it reports an error in this way.
     * This should be used to indicate errors in .proto files which prevent the
     * code generator from generating correct code.  Errors which indicate a
     * problem in protoc itself -- such as the input CodeGeneratorRequest being
     * unparseable -- should be reported by writing a message to stderr and
     * exiting with a non-zero status code.
     *
     * Generated from protobuf field <code>optional string error = 1;</code>
     * @return string
     */
    public function getError()
    {
        return isset($this->error) ? $this->error : '';
    }

    public function hasError()
    {
        return isset($this->error);
    }

    public function clearError()
    {
        unset($this->error);
    }

    /**
     * Error message.  If non-empty, code generation failed.  The plugin process
     * should exit with status code zero even if it reports an error in this way.
     * This should be used to indicate errors in .proto files which prevent the
     * code generator from generating correct code.  Errors which indicate a
     * problem in protoc itself -- such as the input CodeGeneratorRequest being
     * unparseable -- should be reported by writing a message to stderr and
     * exiting with a non-zero status code.
     *
     * Generated from protobuf field <code>optional string error = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setError($var)
    {
        GPBUtil::checkString($var, True);
        $this->error = $var;

        return $this;
    }

    /**
     * A bitmask of supported features that the code generator supports.
     * This is a bitwise "or" of values from the Feature enum.
     *
     * Generated from protobuf field <code>optional uint64 supported_features = 2;</code>
     * @return int|string
     */
    public function getSupportedFeatures()
    {
        return isset($this->supported_features) ? $this->supported_features : 0;
    }

    public function hasSupportedFeatures()
    {
        return isset($this->supported_features);
    }

    public function clearSupportedFeatures()
    {
        unset($this->supported_features);
    }

    /**
     * A bitmask of supported features that the code generator supports.
     * This is a bitwise "or" of values from the Feature enum.
     *
     * Generated from protobuf field <code>optional uint64 supported_features = 2;</code>
     * @param int|string $var
     * @return $this
     */
    public function setSupportedFeatures($var)
    {
        GPBUtil::checkUint64($var);
        $this->supported_features = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>repeated .google.protobuf.compiler.CodeGeneratorResponse.File file = 15;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Generated from protobuf field <code>repeated .google.protobuf.compiler.CodeGeneratorResponse.File file = 15;</code>
     * @param \Google\Protobuf\Compiler\CodeGeneratorResponse\File[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setFile($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Protobuf\Compiler\CodeGeneratorResponse\File::class);
        $this->file = $arr;

        return $this;
    }

}

