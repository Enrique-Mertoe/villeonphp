<?php

namespace SMVTemplating;
//class Interpolator
//{
//    private static string $VAR_START_TAG = "{{";
//    private static string $VAR_END_TAG = "}}";
//    private static string $STRUCT_START_TAG = "{%";
//    private static string $STRUCT_END_TAG = "%}";
//
//    private array $contentArgs = [];
//
//    private bool $levelEnd = false;
//    private int $round = 0;
//
//
//    public static function bind($content, $vars): string
//    {
//        return (new Interpolator())->begin($content, $vars);
//    }
//
//    private function begin($content, $vars): string
//    {
//        $this->contentArgs = $vars;
//        return $this->interpolate($content, $vars);
//    }
//
//    private function processStackArgs($type, $args): mixed
//    {
//        if ($type == "for") {
//            return ["key" => $args["loopVar"],
//                "value" => $this->parseIterable($args["iterable"])];
//        }
//        return null;
//    }
//
//    private function interpolate($content, $args): string
//    {
//        print_r("\n------------------input--------------------------------\n");
//        print_r($content);
//
//        $output = '';
//        $length = strlen($content);
//        $levelStackArgs = [];
//        $stackContent = "";
//        $lStacks = [];
//        $last = '';
//        $cEnd = false;
//
//        $i = 0;
//        while ($i < $length) {
//            if (strpos($content, self::$STRUCT_START_TAG, $i) === $i) {
//                $endPos = strpos($content, self::$STRUCT_END_TAG, $i);
//                if ($endPos !== false) {
//                    $tagContent = substr($content, $i + 2, $endPos - $i - 2);
//                    $struct = $this->processControlStructure(trim($tagContent));
//                    $end = $struct["end"] ?? null;
//                    $s_args = $struct["args"] ?? null;
//                    $type = $struct["type"] ?? null;
//                    $hasEnd = $struct["hasEnd"] ?? null;
//                    $stuckNum = count($lStacks);
//                    if ($stuckNum > 0) {
//                        if (!($end && $stuckNum == 1))
//                            $stackContent .= "{%$tagContent%}";
//                    }
//                    if ($hasEnd) {
//                        $lStacks[] = $type;
//                        if ($stuckNum == 0)
//                            $levelStackArgs[] = $this->processStackArgs($type ?? "", $s_args);
//                    }
//                    if ($end) {
//                        if ($stuckNum == 1) {
//                            $cEnd = true;
//                        } else
//                            array_pop($lStacks);
//                    }
//                    $i = $endPos + 2;
//                    continue;
//                }
//            } else {
//                if (count($lStacks)) {
//                    if ($cEnd) {
//                        $output .= $this->doLevelContent($stackContent, $lStacks[0], $args, $levelStackArgs);
//                        $levelStackArgs = [];
//                        $stackContent = "";
//                        $cEnd = false;
//                    } else
//                        $stackContent .= $content[$i];
//                } else {
//                    $output .= $content[$i];
//                }
//            }
//            $i++;
//        }
//        print_r("\n------------------------output-----------------------------------\n");
//        print_r($output);
//        return $output;
//    }
//
//    private function doLevelContent($content, $type, $args, $extra_args): string
//    {
//
//        $final_content = '';
//        switch ($type) {
//            case "for":
//                $final_content .= $this->performLoop($content, $args, $extra_args[0]);
//                break;
//            case "if":
//                $final_content .= $this->performCondition($content);
//                break;
//            default:
//                break;
//
//        }
//        return $final_content;
//    }
//
//    private function performLoop($content, $args, $items): string
//    {
//        $output = "";
//        if ($items) {
//            $value = $items["value"];
//            foreach ($value as $item) {
//                $args[$items["key"]] = $item;
//                $output .= $this->interpolate($content, $args);
//            }
//        }
//        return $output;
//    }
//
//    private function performCondition($content)
//    {
//        if (is_array($this->contentArgs))
//            return $content;
//        return "";
//    }
//
//    private function interpolateVariable(string $varName, $variables): string
//    {
//        $var_filter = explode("|", $varName);
//        if (count($var_filter) > 0) {
//            $varName = $this->getVariableValue($variables, $var_filter[0]);
//            return is_array($varName) ? json_encode($varName) : $varName ?? "";
//        }
//        return '';
//    }
//
//    private function processControlStructure(string $tag): array
//    {
//        if (str_starts_with($tag, 'if')) {
//            return ['type' => 'if',
//                'args' => $this->extractIfArgs($tag),
//                'hasEnd' => true];
//        } elseif (str_starts_with($tag, 'for')) {
//            return ['type' => 'for',
//                'args' => $this->extractForArgs($tag),
//                'hasEnd' => true];
//        } elseif (str_starts_with($tag, 'include')) {
//            return ['type' => 'include',
//                'args' => $this->extractIncludeArgs($tag),
//                'hasEnd' => false];
//        } elseif (str_starts_with($tag, 'end')) {
//            return ['end' => true,
//                'args' => $this->extractIncludeArgs($tag),
//                'hasEnd' => false];
//        }
//        return [null, null, false];
//    }
//
//    private function extractIncludeArgs(string $tag): string|array
//    {
//        // Extract file or variable from include statement
//        preg_match('/include\s+(.*)/', $tag, $matches);
//        return $matches[1] ?? [];
//    }
//
//    private function extractIfArgs(string $tag): array|string
//    {
//        // Extract condition from the if statement
//        // Example: "if condition" -> return condition as an array or string
//        preg_match('/if\s+(.*)/', $tag, $matches);
//        return $matches[1] ?? [];
//    }
//
//    private function extractForArgs(string $tag): array
//    {
//        // Extract loop variable and iterable from the for statement
//        preg_match('/for\s+(\w+)\s+in\s+(.+)/', $tag, $matches);
//        return [
//            'loopVar' => $matches[1] ?? '',
//            'iterable' => $matches[2] ?? ''
//        ];
//    }
//
//    private function handleForLoop($tag): array
//    {
//        if (!preg_match('/for\s+(\w+)\s+in\s+(.+)/', $tag, $matches)) {
//            throw new \Exception("Invalid for loop syntax: $tag");
//        }
//        $loopVar = $matches[1];
//        $iterable = $matches[2];
//        $items = $this->parseIterable($iterable);
//        return [
//            "key" => $loopVar,
//            "value" => $items,
//        ];
//    }
//
//    private function parseIterable(string $iterable): array
//    {
//        if (preg_match('/range\((\d+),\s*(\d+)\)/', $iterable, $matches)) {
//            $start = (int)$matches[1];
//            $end = (int)$matches[2];
//            return range($start, $end);
//        }
//
//        if ($iterable[0] === '[' && $iterable[-1] === ']') {
//            $arrayContent = trim($iterable, '[]');
//            return array_map('trim', explode(',', $arrayContent));
//        }
//        return $this->getVariableValue($this->contentArgs, $iterable) ?? [];
//    }
//
//    private function getVariableValue(array $variables, string $path)
//    {
//        $keys = explode('.', trim($path));
//        $value = $variables;
//        foreach ($keys as $key) {
//            if (array_key_exists($key, is_array($value) ? $value : [])) {
//                $value = $value[$key];
//            } else {
//                return null;
//            }
//        }
//
//        return $value;
//    }
//}


class Interpolator
{
    private static string $VAR_START_TAG = "{{";
    private static string $VAR_END_TAG = "}}";
    private static string $STRUCT_START_TAG = "{%";
    private static string $STRUCT_END_TAG = "%}";

    private array $contentArgs = [];

    private bool $levelEnd = false;
    private int $round = 0;


    public static function bind($content, $vars): string
    {
        return (new Interpolator())->begin($content, $vars);
    }

    private function begin($content, $vars): string
    {
        $this->contentArgs = $vars;
        return $this->interpolate($content, $vars);
    }

    private function interpolate($content, $args): string
    {
        $output = '';
        $length = strlen($content);
        $levelStackArgs = [];
        $stackContent = "";
        $lStacks = [];
        $last = '';

        $i = 0;
        while ($i < $length) {
            $stacks = count($lStacks);
            if (strpos($content, self::$STRUCT_START_TAG, $i) === $i) {
                $endPos = strpos($content, self::$STRUCT_END_TAG, $i);
                if ($endPos !== false) {
                    $tagContent = substr($content, $i + 2, $endPos - $i - 2);
                    $struct = $this->processControlStructure(trim($tagContent));

                    $end = $this->levelEnd;
                    $type = $struct["type"] ?? "";
                    $s_args = $struct["args"] ?? null;
                    $hasEnd = $struct["hasEnd"] ?? null;
                    if ($stacks > 0) {
                        if (!($end && $stacks == 1))
                            $stackContent .= "{%$tagContent%}";
                    }
                    if (isset($struct["type"])) {
                        $lStacks[] = $struct["type"];
                        if ($stacks == 0 && $hasEnd)
                            $levelStackArgs[] = $this->processStackArgs($type, $s_args);

                    } else {
                        $last = array_pop($lStacks);
                    }
                    $i = $endPos + 2;
                    continue;
                }
            } elseif ($stacks) {
                $stackContent .= $content[$i];
            } elseif (strpos($content, self::$VAR_START_TAG, $i) === $i) {
                $endPos = strpos($content, self::$VAR_END_TAG, $i);
                if ($endPos !== false) {
                    $varName = substr($content, $i + 2, $endPos - $i - 2);
                    $value = $this->interpolateVariable($varName, $args);
                    $output .= $value;
                    $i = $endPos + 2;
                    continue;
                }
            } else {
                if ($this->levelEnd) {
                    $this->levelEnd = false;
                    $output .= $this->doLevelContent($stackContent, $last, $args, $levelStackArgs);
                    $stackContent = "";
                }
                $output .= $content[$i];

            }
            $i++;
        }
        return $output;
    }

    private function processStackArgs($type, $args): ?array
    {
        if ($type == "for") {
            return ["key" => $args["loopVar"],
                "value" => $this->parseIterable($args["iterable"])];
        }
        if ($type == "if") {
            $pattern = '/\s*(==|===|!=|!==|>=|<=|>|<|&&|\|\|)\s*/';
            $parts = preg_split($pattern, $args);
            if (count($parts) > 1){
                print_r($parts);
            }
        }
        return null;
    }

    private function doLevelContent($content, $type, $args, &$extra_args): string
    {
        $final_content = '';
        switch ($type) {
            case "for":
                $final_content .= $this->performLoop($content, $args, $extra_args);
                break;
            case "if":
                $final_content .= $this->performCondition($content, $extra_args);
                break;
            default:
                break;

        }
        return $final_content;
    }

    private function performLoop($content, $args, &$items): string
    {
        $output = "";
        $items = array_pop($items);
        if ($items) {
            $value = $items["value"];
            foreach ($value as $item) {
                $args[$items["key"]] = $item;
                $output .= $this->interpolate($content, $args);
            }
        }
        return $output;
    }

    private function performCondition($content, $args)
    {
//        print_r($args);
        if (is_array($this->contentArgs))
            return $content;
        return "";
    }

    private function interpolateVariable(string $varName, $variables): string
    {
        $var_filter = explode("|", $varName);
        if (count($var_filter) > 0) {
            $varName = $this->getVariableValue($variables, $var_filter[0]);
            return is_array($varName) ? json_encode($varName) : $varName ?? "";
        }
        return '';
    }

    private function processControlStructure(string $tag): array
    {
        if (str_starts_with($tag, 'if')) {
            return ['type' => 'if', 'args' => $this->extractIfArgs($tag), 'hasEnd' => true];
        } elseif (str_starts_with($tag, 'for')) {
            return ['type' => 'for', 'args' => $this->extractForArgs($tag), 'hasEnd' => true];
        } elseif (str_starts_with($tag, 'include')) {
            return ['type' => 'include', 'args' => $this->extractIncludeArgs($tag), 'hasEnd' => false];
        } elseif (str_starts_with($tag, 'end')) {
            $this->levelEnd = true;
        }
        return [null, null, false];
    }

    private function extractIncludeArgs(string $tag): string
    {
        // Extract file or variable from include statement
        preg_match('/include\s+(.*)/', $tag, $matches);
        return $matches[1] ?? [];
    }

    private function extractIfArgs(string $tag): string|array
    {
        preg_match('/if\s+(.*)/', $tag, $matches);
        return $matches[1] ?? [];
    }

    private function extractForArgs(string $tag): array
    {
        // Extract loop variable and iterable from the for statement
        preg_match('/for\s+(\w+)\s+in\s+(.+)/', $tag, $matches);
        return [
            'loopVar' => $matches[1] ?? '',
            'iterable' => $matches[2] ?? ''
        ];
    }

    private function handleForLoop($tag): array
    {
        if (!preg_match('/for\s+(\w+)\s+in\s+(.+)/', $tag, $matches)) {
            throw new \Exception("Invalid for loop syntax: $tag");
        }
        $loopVar = $matches[1];
        $iterable = $matches[2];
        $items = $this->parseIterable($iterable);
        return [
            "key" => $loopVar,
            "value" => $items,
        ];
    }

    private function parseIterable(string $iterable): array
    {
        if (preg_match('/range\((\d+),\s*(\d+)\)/', $iterable, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            return range($start, $end);
        }

        if ($iterable[0] === '[' && $iterable[-1] === ']') {
            $arrayContent = trim($iterable, '[]');
            return array_map('trim', explode(',', $arrayContent));
        }
        return $this->getVariableValue($this->contentArgs, $iterable) ?? [];
    }

    private function getVariableValue(array $variables, string $path)
    {
        $keys = explode('.', trim($path));
        $value = $variables;
        foreach ($keys as $key) {
            if (array_key_exists($key, is_array($value) ? $value : [])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }

        return $value;
    }
}