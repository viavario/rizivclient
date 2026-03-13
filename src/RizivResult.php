<?php

declare (strict_types = 1);

namespace viavario\rizivclient;

/**
 * Represents a single healthcare professional result from the RIZIV search page.
 */
class RizivResult
{
    /** @var string */
    public $name;

    /** @var string */
    public $riziv_number;

    /** @var string */
    public $profession;

    /** @var bool */
    public $contracted;

    /** @var string */
    public $qualification;

    /** @var \DateTime */
    public $qualification_date;

    /**
     * @param string $name                 The healthcare professional's full name.
     * @param string $riziv_number         The RIZIV number.
     * @param string $profession           The profession.
     * @param bool   $contracted           Whether contracted.
     * @param string $qualification        The qualification.
     * @param \DateTime $qualification_date   The qualification date.
     */
        public function __construct(string $name, string $riziv_number, string $profession, bool $contracted, string $qualification, \DateTime $qualification_date)
    {
        $this->name               = $name;
        $this->riziv_number       = $riziv_number;
        $this->profession         = $profession;
        $this->contracted         = $contracted;
        $this->qualification      = $qualification;
        $this->qualification_date = $qualification_date;
    }

    /**
     * Returns the result as an associative array.
     *
     * @return array{name: string, riziv_number: string, profession: string, contracted: bool, qualification: string, qualification_date: \DateTime}
     */
    public function toArray(): array
    {
        return [
            'name'               => $this->name,
            'riziv_number'       => $this->riziv_number,
            'profession'         => $this->profession,
            'contracted'         => $this->contracted,
            'qualification'      => $this->qualification,
            'qualification_date' => $this->qualification_date,
        ];
    }
}
