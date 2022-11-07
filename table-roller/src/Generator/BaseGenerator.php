<?php


namespace TableRoller\Generator;


use TableRoller\Table\Registry;

class BaseGenerator
{
    protected string $template;
    protected Registry $tables;

    private $allowedFunctions = [
        'trim', 'ucwords', 'ucfirst', 'lcfirst', 'strotolower', 'strtoupper'
    ];

    /**
     * BaseGenerator constructor.
     * @param string $template
     * @param Registry $tables
     */
    public function __construct(Registry $tables)
    {
        $this->tables = $tables;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    private function doReplacements(array $match): string
    {
        $tableName = $match[1];
        $fn = null;
        if (false !== strpos($tableName, '|')) {
            $parts = explode('|', $tableName);
            $tableName = $parts[0];
            $fn = $parts[1];
        }
        // simple onetime roll
        $table = $this->tables->load($tableName);
        $result = $table->rollOnce();

        if (!empty($fn) && in_array($fn, $this->allowedFunctions)) {
            $result = $fn($result);
        }

        return $result;
    }

    public function generate(): string
    {
        if (method_exists($this, 'getTemplate')) {
            $template = $this->getTemplate();
        } elseif (!empty($this->template)) {
            $template = $this->template;
        }

        if (empty($template)) {
            throw new \Exception('getTemplate()/template property is empty');
        }

        return preg_replace_callback('/\[([^]]+)\]/', [$this, 'doReplacements'], $template);
    }

    public function __toString()
    {
        return $this->generate();
    }

    public function __invoke() : string
    {
        return $this->generate();
    }


}