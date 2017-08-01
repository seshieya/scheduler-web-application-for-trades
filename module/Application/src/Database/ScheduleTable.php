<?php

namespace Application\Database;

class ScheduleTable extends BaseTable
{
    public function insertScheduleData(array $data) {
        $insert = $this->sql
            ->insert('schedule')
            ->values($data);

        $query = $this->sql->buildSqlString($insert);

        return $this->adapter->query($query)->execute();
    }
}