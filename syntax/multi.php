<?php

/**
 * DokuWiki Plugin randomtable multi (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Oscar Merida <oscar@oscarm.org>
 */
class syntax_plugin_randomtables_multi extends \dokuwiki\Extension\SyntaxPlugin
{
    /** @inheritDoc */
    public function getType(): string
    {
        return 'protected';
    }

    /** @inheritDoc */
    public function getPType(): string
    {
        return 'block';
    }

    /** @inheritDoc */
    public function getSort(): int
    {
        return 201;
    }

    /** @inheritDoc */
    public function connectTo($mode): void
    {
        $this->Lexer->addEntryPattern('\<ROLL_MULTI\s+', $mode, 'plugin_randomtables_multi');
    }

    /** @inheritDoc */
    public function postConnect(): void
    {
        $this->Lexer->addExitPattern('>', 'plugin_randomtables_multi');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler): array
    {
        switch ($state) {
            case DOKU_LEXER_UNMATCHED:
                return [$state, $match];
            case DOKU_LEXER_EXIT:
                return [$state, ''];
            case DOKU_LEXER_ENTER:
            default:
                return [$state, $match];
        }
    }

    /** @inheritDoc */
    public function render($mode, Doku_Renderer $renderer, $data): bool
    {
        if ($mode !== 'xhtml') {
            return false;
        }
        [$state, $match] = $data;

        switch ($state) {
            case DOKU_LEXER_ENTER:
                $renderer->doc .= '<div class="randomtable-well randomtable-well-column">' . PHP_EOL;
                break;
            case DOKU_LEXER_UNMATCHED:
                $id = md5(__CLASS__ . serialize($data));

                $tables = preg_split('/[\r\n]+/', $match);
                $tables = array_filter($tables);
                $renderer->doc .= '<div class="btnGroup">';
                foreach ($tables as $table) {
                    if (str_contains($table, ':')) {
                        [$ident, $label] = explode(':', $table,2);
                    } else {
                        $ident = $label = $table;
                    }

                    $ident =  $renderer->_xmlEntities(trim($ident));
                    $label =  $renderer->_xmlEntities(trim($label));

                    $id = $renderer->_xmlEntities($id);
                    $renderer->doc .= '<button class="randomtable" data-src="'
                        . $ident . '" data-target="results-'
                        . $id . '">Roll ' . $label
                        . '</button>';
                }
                $renderer->doc .= '</div><div id="results-' . $id . '" class="results"></div>';
                break;
            case DOKU_LEXER_EXIT:
                $renderer->doc .= '</div>' . PHP_EOL;
                break;
        }
        return true;
    }
}
