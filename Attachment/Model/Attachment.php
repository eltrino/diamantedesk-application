<?php
/**
 * Created by PhpStorm.
 * User: Ruslan Voitenko
 * Date: 7/1/14
 * Time: 4:57 PM
 */

namespace Eltrino\DiamanteDeskBundle\Attachment\Model;

class Attachment
{
    const ATTACHMENTS_DIRECTORY = 'attachment';

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct($filename)
    {
        $this->filename  = $filename;
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
