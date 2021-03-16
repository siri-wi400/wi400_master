<?php

namespace PhpOffice\PhpSpreadsheet\Worksheet;

class ColumnDimension extends Dimension
{
    /**
     * Column index.
     *
     * @var string
     */
    private $columnIndex;

    /**
     * Column width.
     *
     * When this is set to a negative value, the column width should be ignored by IWriter
     *
     * @var float
     */
    private $width = -1;

    /**
     * Auto size?
     *
     * @var bool
     */
    private $autoSize = false;
    
    // SIRI: Modifica per poter settare dei margini diversi per la cella (in particolare in Autosize)
    private $_margin = 0;

    /**
     * Create a new ColumnDimension.
     *
     * @param string $pIndex Character column index
     */
    public function __construct($pIndex = 'A')
    {
        // Initialise values
        $this->columnIndex = $pIndex;

        // set dimension as unformatted by default
        parent::__construct(0);
    }

    /**
     * Get column index as string eg: 'A'.
     *
     * @return string
     */
    public function getColumnIndex()
    {
        return $this->columnIndex;
    }

    /**
     * Set column index as string eg: 'A'.
     *
     * @param string $pValue
     *
     * @return $this
     */
    public function setColumnIndex($pValue)
    {
        $this->columnIndex = $pValue;

        return $this;
    }

    /**
     * Get Width.
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set Width.
     *
     * @param float $pValue
     *
     * @return $this
     */
    public function setWidth($pValue)
    {
        $this->width = $pValue;

        return $this;
    }

    /**
     * Get Auto Size.
     *
     * @return bool
     */
    public function getAutoSize()
    {
        return $this->autoSize;
    }

    /**
     * Set Auto Size.
     *
     * @param bool $pValue
     *
     * @return $this
     */
    public function setAutoSize($pValue)
    {
        $this->autoSize = $pValue;

        return $this;
    }
    
// SIRI INIZIO
    // Metodo per l'impostazione della dimensione del margine delle celle
    public function setMargin($margin=0) {
    	$this->_margin = $margin;
    }
    
    // Metodo per recuperare la dimensione del margine delle celle
    public function getMargin() {
    	return $this->_margin;
    }
// SIRI FINE
}
