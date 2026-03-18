<?php

namespace App\Repositories\Logix;

use Illuminate\Support\Collection;

class BancoCreditNoteRepository extends BaseLogixRepository
{
    private const TABLE = 'LOGIXPRD.SC_BANCO_CREDIT_NOTE';

    public function all(): Collection
    {
        return $this->query("
            SELECT
                ID_REGISTRO,
                TRIM(COD_EMPRESA) AS cod_empresa,
                NUM_NC,
                ANO_NC,
                TRIM(ACCOUNT_NAME) AS account_name,
                TRIM(BANK_NAME) AS bank_name,
                TRIM(ACCOUNT_TYPE) AS account_type,
                TRIM(ACCOUNT_NUMBER) AS account_number,
                TRIM(IBAN) AS iban,
                TRIM(SWIFT_CODE) AS swift_code,
                TRIM(BRANCH) AS branch
            FROM " . self::TABLE . "
            ORDER BY COD_EMPRESA, NUM_NC, ANO_NC
        ")->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    public function search(array $params): Collection
    {
        $where = ['1=1'];
        $bindings = [];

        if (! empty($params['cod_empresa'])) {
            $where[] = 'COD_EMPRESA = :cod_empresa';
            $bindings['cod_empresa'] = $params['cod_empresa'];
        }

        if (! empty($params['num_nc'])) {
            $where[] = 'NUM_NC = :num_nc';
            $bindings['num_nc'] = $params['num_nc'];
        }

        if (! empty($params['ano_nc'])) {
            $where[] = 'ANO_NC = :ano_nc';
            $bindings['ano_nc'] = $params['ano_nc'];
        }

        $whereClause = implode(' AND ', $where);

        return $this->query("
            SELECT
                ID_REGISTRO,
                TRIM(COD_EMPRESA) AS cod_empresa,
                NUM_NC,
                ANO_NC,
                TRIM(ACCOUNT_NAME) AS account_name,
                TRIM(BANK_NAME) AS bank_name,
                TRIM(ACCOUNT_TYPE) AS account_type,
                TRIM(ACCOUNT_NUMBER) AS account_number,
                TRIM(IBAN) AS iban,
                TRIM(SWIFT_CODE) AS swift_code,
                TRIM(BRANCH) AS branch
            FROM " . self::TABLE . "
            WHERE {$whereClause}
            ORDER BY COD_EMPRESA, NUM_NC, ANO_NC
        ", $bindings)->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));
    }

    public function find(int $id): ?object
    {
        $rows = $this->query("
            SELECT
                ID_REGISTRO,
                TRIM(COD_EMPRESA) AS cod_empresa,
                NUM_NC,
                ANO_NC,
                TRIM(ACCOUNT_NAME) AS account_name,
                TRIM(BANK_NAME) AS bank_name,
                TRIM(ACCOUNT_TYPE) AS account_type,
                TRIM(ACCOUNT_NUMBER) AS account_number,
                TRIM(IBAN) AS iban,
                TRIM(SWIFT_CODE) AS swift_code,
                TRIM(BRANCH) AS branch
            FROM " . self::TABLE . "
            WHERE ID_REGISTRO = :id
        ", ['id' => $id]);

        if ($rows->isEmpty()) {
            return null;
        }

        return (object) array_change_key_case((array) $rows->first(), CASE_LOWER);
    }

    public function insert(array $data): int
    {
        $this->connection()->insert("
            INSERT INTO " . self::TABLE . " (
                COD_EMPRESA, NUM_NC, ANO_NC, ACCOUNT_NAME, BANK_NAME,
                ACCOUNT_TYPE, ACCOUNT_NUMBER, IBAN, SWIFT_CODE, BRANCH,
                USUARIO_INCL, DATA_INCL
            ) VALUES (
                :cod_empresa, :num_nc, :ano_nc, :account_name, :bank_name,
                :account_type, :account_number, :iban, :swift_code, :branch,
                :usuario, SYSDATE
            )
        ", [
            'cod_empresa'    => $data['cod_empresa'],
            'num_nc'         => $data['num_nc'],
            'ano_nc'         => $data['ano_nc'],
            'account_name'   => $data['account_name'] ?? null,
            'bank_name'      => $data['bank_name'] ?? null,
            'account_type'   => $data['account_type'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'iban'           => $data['iban'] ?? null,
            'swift_code'     => $data['swift_code'] ?? null,
            'branch'         => $data['branch'] ?? null,
            'usuario'        => $data['usuario'],
        ]);

        $result = $this->query("
            SELECT MAX(ID_REGISTRO) AS id FROM " . self::TABLE . "
            WHERE COD_EMPRESA = :cod_empresa AND NUM_NC = :num_nc AND ANO_NC = :ano_nc
        ", [
            'cod_empresa' => $data['cod_empresa'],
            'num_nc'      => $data['num_nc'],
            'ano_nc'      => $data['ano_nc'],
        ]);

        return (int) $result->first()->id;
    }

    public function update(int $id, array $data): void
    {
        $this->connection()->update("
            UPDATE " . self::TABLE . " SET
                COD_EMPRESA    = :cod_empresa,
                NUM_NC         = :num_nc,
                ANO_NC         = :ano_nc,
                ACCOUNT_NAME   = :account_name,
                BANK_NAME      = :bank_name,
                ACCOUNT_TYPE   = :account_type,
                ACCOUNT_NUMBER = :account_number,
                IBAN           = :iban,
                SWIFT_CODE     = :swift_code,
                BRANCH         = :branch,
                USUARIO_ALTE   = :usuario,
                DATA_ALTE      = SYSDATE
            WHERE ID_REGISTRO = :id
        ", [
            'cod_empresa'    => $data['cod_empresa'],
            'num_nc'         => $data['num_nc'],
            'ano_nc'         => $data['ano_nc'],
            'account_name'   => $data['account_name'] ?? null,
            'bank_name'      => $data['bank_name'] ?? null,
            'account_type'   => $data['account_type'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'iban'           => $data['iban'] ?? null,
            'swift_code'     => $data['swift_code'] ?? null,
            'branch'         => $data['branch'] ?? null,
            'usuario'        => $data['usuario'],
            'id'             => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->connection()->delete(
            "DELETE FROM " . self::TABLE . " WHERE ID_REGISTRO = :id",
            ['id' => $id]
        );
    }
}
