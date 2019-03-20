<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

/**
 * Grupo dos enpoints iniciados por v1
 */
$app->group('/v1', function() {
    $this->group('/backend', function() {
        $this->get('', function (Request $request, Response $response, array $args) {
            $sql = 'SELECT regional, SUM(sim) AS total_sim, COUNT(*) AS total_regional '
                . 'FROM ( '
                . '  SELECT st.regional, '
                . "  CASE WHEN UPPER(al.description) = 'SIM' THEN 1 ELSE 0 END AS sim "
                . '  FROM students st '
                . '  JOIN answers an ON an.student_id = st.id '
                . '  JOIN questions qu ON qu.id = an.question_id '
                . '  JOIN alternatives al ON al.id = an.alternative_id '
                . ') AS t '
                . 'GROUP BY regional '
                . 'ORDER BY regional';

            $sth = $this->db->prepare($sql);
            $sth->execute();
            $regionalsAll = $sth->fetchAll();

            $regionals = array();
            $totalSim = 0;
            $totalRegionais = 0;

            foreach ($regionalsAll as $regional) {
                $regionals[] = array(
                    'description' => $regional['regional'],
                    'average' => number_format((($regional['total_sim'] / $regional['total_regional']) * 100), 4),
                );

                $totalSim += $regional['total_sim'];
                $totalRegionais += $regional['total_regional'];
            }

            $national = number_format((($totalSim / $totalRegionais) * 100), 4);

            return $this->response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withJson(array('regionals' => $regionals, 'national' => $national));
        });
    });

    $this->get('/frontend', function (Request $request, Response $response, array $args) {
        return $this->renderer->render($response, 'frontend.phtml', $args);
    });
});