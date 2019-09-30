<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    SQLLogger.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Logging;

/**
 * Class SQLLogger
 *
 * @package Devrun\Doctrine\Logging
 */
class SQLLogger implements \Doctrine\DBAL\Logging\SQLLogger
{

    /** @var array */
    private $queries = [];


    /**
     * Logs a SQL statement somewhere.
     *
     * @param string     $sql    The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->queries[] = ['dql' => $sql, 'params' => $params, 'types' => $types];
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        $keys  = array_keys($this->queries);
        $key   = end($keys);
        $query = $this->queries[$key];
        $sql   = $query['dql'];

        $sql = str_replace('"START TRANSACTION"', 'START TRANSACTION', $sql);
        $sql = str_replace('"ROLLBACK"', 'ROLLBACK', $sql);
        $sql = str_replace('"COMMIT"', 'COMMIT', $sql);

        $types = $query['types'];

        if (($params = $query['params'])) {

            $quoteParams = [];
            foreach ($params as $index => $param) {
                if ($types[$index] == 'string' || $types[$index] == 'text') {
                    $quoteParams[$index] = $param === null ? 'null' : "'" . (string)$param . "'";

                } elseif ($types[$index] == 'integer' || $types[$index] == 'smallint') {
                    $quoteParams[$index] = $param === null ? 'null' : $param;

                } elseif ($types[$index] == 'boolean') {
                    if ($param === null) $quoteParams[$index] = 'null';
                    else $quoteParams[$index] = $param ? 'true' : 'false';

                } elseif ($types[$index] == 'datetime') {
                    $quoteParams[$index] = $param === null ? 'null' : "'" . (string)$param . "'";

                } elseif ($types[$index] == 'json_array') {
                    $quoteParams[$index] = $param === null ? 'null' : "'" . json_encode($param) . "'";

                } else {
                    $out = is_scalar($param) ? $param : implode(', ', $param);
                    throw new InvalidArgumentException("Unknown type `{$types[$index]}` [value `$out`] in $sql" );
                }
            }

            $sql = preg_replace_callback('/\?/', function ($match) use (&$quoteParams) {
                return array_shift($quoteParams);
            }, $sql);
        }

        $this->queries[$key]['sql'] = "$sql;";
    }


    /**
     * @return array
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @return array
     */
    public function getSqlQueries(): array
    {
        $result = [];
        foreach ($this->queries as $query) {
            $result[] = $query['sql'];
        }

        return $result;
    }

}