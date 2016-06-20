<?php

namespace XmlSchemaValidator;

/**
 * This is an utility class to check a file mime against a set of mimes
 *
 * @access private
 * @package XmlSchemaValidator
 */
class FileMimeChecker extends SetStrings
{
    /** @var \finfo */
    private $getMimeFileInfo;

    protected function getMimeFileInfo()
    {
        if (null === $this->getMimeFileInfo) {
            $this->getMimeFileInfo = new \finfo(FILEINFO_SYMLINK);
        }
        return $this->getMimeFileInfo;
    }

    public function getMimeType($filename)
    {
        if (! (is_file($filename) || is_link($filename)) || ! is_readable($filename)) {
            return '';
        }
        return (string) $this->getMimeFileInfo()->file($filename, FILEINFO_MIME_TYPE);
    }

    public function check($filename)
    {
        return $this->checkMime($this->getMimeType($filename));
    }

    public function checkMime($mimetype)
    {
        $mimetype = $this->cast($mimetype);
        if (0 === $this->count()) {
            return true;
        }
        if ('' === $mimetype) {
            return false;
        }
        return array_key_exists($mimetype, $this->members);
    }

    public function cast($member)
    {
        return strtolower(trim(parent::cast($member)));
    }
}
