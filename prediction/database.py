#!/usr/bin/env python3

from typing import List
import os
import psycopg2
from psycopg2.extras import execute_values

class Database:
    def __init__(self):
        self.__connection = psycopg2.connect(
            host=os.environ['DB_HOST'],
            database=os.environ['DB_NAME'],
            user=os.environ['DB_USER'],
            password=os.environ['DB_PASSWORD']
        )

    def __del__(self):
        self.__connection.close()

    def get_resumes(self):
        cursor = self.__connection.cursor()
        columns = ['id', 'about', 'experience']
        cursor.execute("SELECT {} FROM candidate ORDER BY id".format(', '.join(columns)))
        candidates = []
        for item in cursor.fetchall():
            candidate = dict(zip(columns, item))
            cursor.execute(f"SELECT s.name FROM candidate_skill cs JOIN skill s ON cs.skill_code = s.code WHERE cs.candidate_id = {candidate.get('id')}")
            candidate['skill_set'] = list(map(lambda x: x[0], cursor.fetchall()))
            candidate['description'] = candidate.get('about')
            candidates.append(candidate)

        cursor.close()
        return candidates

    def get_vacancies(self):
        cursor = self.__connection.cursor()
        columns = ['id', 'description']
        cursor.execute("SELECT {} FROM vacancy ORDER BY id".format(', '.join(columns)))
        vacancies = []
        for item in cursor.fetchall():
            vacancy = dict(zip(columns, item))
            vacancies.append(vacancy)

        cursor.close()
        return vacancies

    def save_relevance(self, data: List[tuple]):
        cursor = self.__connection.cursor()
        upsert_query = 'INSERT INTO relevance (candidate_id, vacancy_id, fit) VALUES %s ON CONFLICT (candidate_id, vacancy_id) DO UPDATE fit = excluded.fit'
        psycopg2.extras.execute_values (
            cursor, upsert_query, data, template=None, page_size=1000
        )
        cursor.commit()
