<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class PackingListRepository extends BaseLogixRepository
{
    /**
     * Search master records (P) with filters.
     */
    public function search(array $params): Collection
    {
        $where = ['1 = 1'];
        $bindings = [];

        if (! empty($params['cod_empresa'])) {
            $where[] = 'TRIM(P.COD_EMPRESA) = :cod_empresa';
            $bindings['cod_empresa'] = trim($params['cod_empresa']);
        }

        if (! empty($params['processo'])) {
            $where[] = 'TRIM(P.PROCESSO) = :processo';
            $bindings['processo'] = trim($params['processo']);
        }

        if (! empty($params['ano'])) {
            $where[] = 'TRIM(P.ANO) = :ano';
            $bindings['ano'] = trim($params['ano']);
        }

        if (! empty($params['embarque'])) {
            $where[] = 'TRIM(P.EMBARQUE) = :embarque';
            $bindings['embarque'] = trim($params['embarque']);
        }

        $whereSql = implode(' AND ', $where);

        $sql = "
            SELECT P.COD_TB_PL_P AS id,
                   TRIM(P.COD_EMPRESA) AS cod_empresa,
                   TRIM(P.PROCESSO) AS processo,
                   TRIM(P.ANO) AS ano,
                   TRIM(P.EMBARQUE) AS embarque,
                   P.NUM_PEDIDO AS num_pedido,
                   TRIM(P.COD_ITEM) AS cod_item,
                   P.USUARIO AS usuario
            FROM RELATORIOS.SC_TAB_PACKING_LIST_P P
            WHERE {$whereSql}
            ORDER BY P.ANO DESC, P.PROCESSO DESC, P.EMBARQUE
        ";

        return $this->query($sql, $bindings);
    }

    /**
     * Get records from the current and previous year only.
     */
    public function getRecent(): Collection
    {
        return $this->query("
            SELECT P.COD_TB_PL_P AS id,
                   TRIM(P.COD_EMPRESA) AS cod_empresa,
                   TRIM(P.PROCESSO) AS processo,
                   TRIM(P.ANO) AS ano,
                   TRIM(P.EMBARQUE) AS embarque,
                   P.NUM_PEDIDO AS num_pedido,
                   TRIM(P.COD_ITEM) AS cod_item,
                   P.USUARIO AS usuario
            FROM RELATORIOS.SC_TAB_PACKING_LIST_P P
            WHERE TRIM(P.ANO) IN (TO_CHAR(SYSDATE, 'YYYY'), TO_CHAR(ADD_MONTHS(SYSDATE, -12), 'YYYY'))
            ORDER BY P.ANO DESC, P.PROCESSO DESC, P.EMBARQUE
        ");
    }

    /**
     * Lookup NUM_PEDIDO from EXP_ITENS by empresa/processo/ano/embarque.
     */
    /**
     * List available NUM_PEDIDO for a given empresa/processo/ano/embarque.
     */
    public function listPedidos(string $empresa, string $processo, string $ano, string $embarque): Collection
    {
        return $this->query(
            "SELECT DISTINCT NUM_PEDIDO AS num_pedido
             FROM LOGIXPRD.EXP_ITENS
             WHERE COD_EMPRESA = :empresa
               AND NUM_PROCESSO = :processo
               AND ANO_PROCESSO = :ano
               AND EMBARQUE = :embarque
             ORDER BY NUM_PEDIDO",
            ['empresa' => trim($empresa), 'processo' => (int) trim($processo), 'ano' => (int) trim($ano), 'embarque' => trim($embarque)]
        );
    }

    /**
     * List available COD_ITEM for a given NUM_PEDIDO.
     */
    public function listItensByPedido(int $numPedido): Collection
    {
        return $this->query(
            "SELECT DISTINCT TRIM(COD_ITEM) AS cod_item
             FROM LOGIXPRD.EXP_ITENS
             WHERE NUM_PEDIDO = :num_pedido
             ORDER BY 1",
            ['num_pedido' => $numPedido]
        );
    }

    /**
     * Check if a master record already exists with same key fields.
     */
    public function masterExists(string $empresa, string $processo, string $ano, string $embarque, int $numPedido, string $codItem, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) AS total FROM RELATORIOS.SC_TAB_PACKING_LIST_P
                WHERE TRIM(COD_EMPRESA) = :empresa
                  AND TRIM(PROCESSO) = :processo
                  AND TRIM(ANO) = :ano
                  AND TRIM(EMBARQUE) = :embarque
                  AND NUM_PEDIDO = :num_pedido
                  AND TRIM(COD_ITEM) = :cod_item";
        $bindings = [
            'empresa' => trim($empresa),
            'processo' => trim($processo),
            'ano' => trim($ano),
            'embarque' => trim($embarque),
            'num_pedido' => $numPedido,
            'cod_item' => trim($codItem),
        ];

        if ($excludeId) {
            $sql .= " AND COD_TB_PL_P != :exclude_id";
            $bindings['exclude_id'] = $excludeId;
        }

        $row = $this->query($sql, $bindings)->first();

        return $row && (int) $row->total > 0;
    }

    /**
     * Get a single master record by ID.
     */
    public function findMaster(int $id): ?object
    {
        $rows = $this->query(
            "SELECT COD_TB_PL_P AS id,
                    TRIM(COD_EMPRESA) AS cod_empresa,
                    TRIM(PROCESSO) AS processo,
                    TRIM(ANO) AS ano,
                    TRIM(EMBARQUE) AS embarque,
                    NUM_PEDIDO AS num_pedido,
                    TRIM(COD_ITEM) AS cod_item,
                    USUARIO AS usuario
             FROM RELATORIOS.SC_TAB_PACKING_LIST_P
             WHERE COD_TB_PL_P = :id",
            ['id' => $id]
        );

        return $rows->first();
    }

    /**
     * Get detail rows for a master record.
     */
    public function getDetails(int $masterId): Collection
    {
        return $this->query(
            "SELECT COD_TB_PL_F AS id,
                    COD_TB_PL_P AS master_id,
                    TO_CHAR(PRODUCTION_DATE, 'YYYY-MM-DD') AS production_date,
                    TO_CHAR(DATE_EXPIRY, 'YYYY-MM-DD') AS date_expiry,
                    CARTONS AS cartons,
                    NET_WEIGHT AS net_weight,
                    GROSS_WEIGHT AS gross_weight,
                    TRIM(LOTS) AS lots,
                    PALETE AS palete,
                    USUARIO AS usuario
             FROM RELATORIOS.SC_TAB_PACKING_LIST_F
             WHERE COD_TB_PL_P = :master_id
             ORDER BY COD_TB_PL_F",
            ['master_id' => $masterId]
        );
    }

    /**
     * Insert a master record and return the new ID.
     */
    public function insertMaster(array $data): int
    {
        $conn = $this->connection();

        $id = (int) $this->query("SELECT RELATORIOS.SEQ_TAB_PL_P.NEXTVAL AS ID FROM DUAL")->first()->id;

        $conn->insert(
            "INSERT INTO RELATORIOS.SC_TAB_PACKING_LIST_P
                (COD_TB_PL_P, COD_EMPRESA, PROCESSO, ANO, EMBARQUE, NUM_PEDIDO, COD_ITEM, USUARIO)
             VALUES (:id, :cod_empresa, :processo, :ano, :embarque, :num_pedido, :cod_item, :usuario)",
            [
                'id'          => $id,
                'cod_empresa' => $data['cod_empresa'],
                'processo'    => $data['processo'],
                'ano'         => $data['ano'],
                'embarque'    => $data['embarque'],
                'num_pedido'  => $data['num_pedido'],
                'cod_item'    => $data['cod_item'],
                'usuario'     => $data['usuario'],
            ]
        );

        return $id;
    }

    /**
     * Update master record.
     */
    public function updateMaster(int $id, array $data): void
    {
        $this->connection()->update(
            "UPDATE RELATORIOS.SC_TAB_PACKING_LIST_P
             SET COD_EMPRESA = :cod_empresa,
                 PROCESSO    = :processo,
                 ANO         = :ano,
                 EMBARQUE    = :embarque,
                 NUM_PEDIDO  = :num_pedido,
                 COD_ITEM    = :cod_item,
                 USUARIO     = :usuario
             WHERE COD_TB_PL_P = :id",
            [
                'id'          => $id,
                'cod_empresa' => $data['cod_empresa'],
                'processo'    => $data['processo'],
                'ano'         => $data['ano'],
                'embarque'    => $data['embarque'],
                'num_pedido'  => $data['num_pedido'],
                'cod_item'    => $data['cod_item'],
                'usuario'     => $data['usuario'],
            ]
        );
    }

    /**
     * Delete master and its details.
     */
    public function deleteMaster(int $id): void
    {
        $conn = $this->connection();
        $conn->delete("DELETE FROM RELATORIOS.SC_TAB_PACKING_LIST_F WHERE COD_TB_PL_P = :id", ['id' => $id]);
        $conn->delete("DELETE FROM RELATORIOS.SC_TAB_PACKING_LIST_P WHERE COD_TB_PL_P = :id", ['id' => $id]);
    }

    /**
     * Insert a detail row.
     */
    public function insertDetail(int $masterId, array $data): int
    {
        $conn = $this->connection();

        $id = (int) $this->query("SELECT RELATORIOS.SEQ_TAB_PL_F.NEXTVAL AS ID FROM DUAL")->first()->id;

        $conn->insert(
            "INSERT INTO RELATORIOS.SC_TAB_PACKING_LIST_F
                (COD_TB_PL_F, COD_TB_PL_P, PRODUCTION_DATE, DATE_EXPIRY, CARTONS, NET_WEIGHT, GROSS_WEIGHT, LOTS, PALETE, USUARIO)
             VALUES (:id, :master_id, TO_DATE(:production_date, 'YYYY-MM-DD'), TO_DATE(:date_expiry, 'YYYY-MM-DD'), :cartons, :net_weight, :gross_weight, :lots, :palete, :usuario)",
            [
                'id'              => $id,
                'master_id'       => $masterId,
                'production_date' => $data['production_date'],
                'date_expiry'     => $data['date_expiry'],
                'cartons'         => $data['cartons'],
                'net_weight'      => $data['net_weight'],
                'gross_weight'    => $data['gross_weight'],
                'lots'            => $data['lots'] ?? null,
                'palete'          => $data['palete'] ?? null,
                'usuario'         => $data['usuario'],
            ]
        );

        return $id;
    }

    /**
     * Update a detail row.
     */
    public function updateDetail(int $id, array $data): void
    {
        $this->connection()->update(
            "UPDATE RELATORIOS.SC_TAB_PACKING_LIST_F
             SET PRODUCTION_DATE = TO_DATE(:production_date, 'YYYY-MM-DD'),
                 DATE_EXPIRY     = TO_DATE(:date_expiry, 'YYYY-MM-DD'),
                 CARTONS         = :cartons,
                 NET_WEIGHT      = :net_weight,
                 GROSS_WEIGHT    = :gross_weight,
                 LOTS            = :lots,
                 PALETE          = :palete,
                 USUARIO         = :usuario
             WHERE COD_TB_PL_F = :id",
            [
                'id'              => $id,
                'production_date' => $data['production_date'],
                'date_expiry'     => $data['date_expiry'],
                'cartons'         => $data['cartons'],
                'net_weight'      => $data['net_weight'],
                'gross_weight'    => $data['gross_weight'],
                'lots'            => $data['lots'] ?? null,
                'palete'          => $data['palete'] ?? null,
                'usuario'         => $data['usuario'],
            ]
        );
    }

    /**
     * Delete a detail row.
     */
    public function deleteDetail(int $id): void
    {
        $this->connection()->delete(
            "DELETE FROM RELATORIOS.SC_TAB_PACKING_LIST_F WHERE COD_TB_PL_F = :id",
            ['id' => $id]
        );
    }
}
