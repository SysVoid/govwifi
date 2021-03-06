<?php

namespace Alphagov\GovWifi;
use DateInterval;
use DateTime;
use PDO;

/**
 * Send specific reports to the Performance Platform.
 * @package Alphagov\GovWifi
 */
abstract class PerformancePlatformReport {
    const DEFAULT_PERIOD = 'day';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DB
     */
    private $db;

    /**
     * @var PerformancePlatformClient
     */
    private $ppClient;

    /**
     * PerformancePlatformReport constructor.
     *
     * @param Config $config
     * @param DB $db
     * @param PerformancePlatformClient $ppClient If not provided, it will default to a new client created
     * based on the values in the configuration.
     */
    public function __construct($config, $db, $ppClient = null) {
        $this->config   = $config;
        $this->db       = $db;
        $this->ppClient = $ppClient;
        if (null === $ppClient) {
            $this->ppClient = $this->getDefaultClient();
        }
    }

    /**
     * Query the database and send the metrics to the Performance Platform.
     * @return mixed
     */
    abstract function sendMetrics();

    /**
     * Return the metric name used for this report.
     * @return string
     */
    abstract function getMetricName();

    /**
     * Sends a simple metric to the Performance Platform.
     *
     * @param array $params
     */
    protected function sendSimpleMetric($params) {
        $dateObject = new DateTime();
        $params = array_merge([
            'categoryName'  => null,
            'categoryValue' => null,
            'sql'           => null,
            'period'        => self::DEFAULT_PERIOD,
            'timestamp'     => $dateObject->sub(new DateInterval('P1D'))->format('Y-m-d') . 'T00:00:00+00:00'
        ], $params);

        if (! is_array($params['sql'])) {
            $params['sql'] = array($params['sql']);
        }
        $data = [];
        foreach ($params['sql'] as $sql) {
            if (! empty($sql)) {
                $results = $this->runQuery($sql);
                //TODO: Check if forcing intval here will be applicable in every instance.
                foreach ($results[0] as $key => $value) {
                    $data[$key] = intval($value);
                }
            }
        }

        $this->ppClient->sendData([
            'bearerToken'   => $this->config->values['performance-platform']['stats'][$this->getMetricName()],
            'timestamp'     => $params['timestamp'],
            'dataType'      => $this->getMetricName(),
            'period'        => $params['period'],
            'categoryName'  => $params['categoryName'],
            'categoryValue' => $params['categoryValue']
        ], $data);
    }

    /**
     * Runs the SQL query provided, returns an associative array of all results.
     *
     * @param string $sql
     * @return array
     */
    protected function runQuery($sql) {
        $handle = $this->db->getConnection()->prepare($sql);
        $handle->execute();
        return $handle->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new instance of the PerformancePlatformClient class based on the configuration values.
     * @return PerformancePlatformClient
     */
    protected function getDefaultClient() {
        return new PerformancePlatformClient([
            'serviceName' => $this->config->values['performance-platform']['service-name'],
            'baseUrl'     => $this->config->values['performance-platform']['base-url']
        ]);
    }
}