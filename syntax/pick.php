<?php

/**
 * DokuWiki Plugin randomtable pick (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Oscar Merida <oscar@oscarm.org>
 */
class syntax_plugin_randomtables_pick extends \dokuwiki\Extension\SyntaxPlugin
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
        $this->Lexer->addEntryPattern('\<ROLL_PICK\s+', $mode, 'plugin_randomtables_pick');
    }

    /** @inheritDoc */
    public function postConnect(): void
    {
        $this->Lexer->addExitPattern('>', 'plugin_randomtables_pick');
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
                $renderer->doc .= '<div class="randomtable-well">' . PHP_EOL;
                break;
            case DOKU_LEXER_UNMATCHED:
                $id = md5(serialize($data));

                $renderer->doc .= '<div><select class="randomtable-pick" id="' . $id. '">';
                $renderer->doc .= '<option value="">Select Table</option>';

                $tables = preg_split('/[\r\n]+/', $match);
                $tables = array_filter($tables);
                foreach ($tables as $table) {
                    if (str_contains($table, ':')) {
                        [$ident, $label] = explode(':', $table,2);
                    } else {
                        $ident = $label = $table;
                    }

                    $ident =  $renderer->_xmlEntities(trim($ident));
                    $label =  $renderer->_xmlEntities(trim($label));
                    $renderer->doc .= "<option value=\"{$ident}\">{$label}</option>";
                }
                $renderer->doc .= '</select>';

                $renderer->doc .= '<button class="randomtable" data-pick="' . $id . '" data-target="results-'
                    . $id . '">Roll</button></div><div id="results-' . $id . '" class="results"></div>';
                break;
            case DOKU_LEXER_EXIT:
                $renderer->doc .= '</div>' . PHP_EOL;
                break;
        }
        return true;
    }
}
