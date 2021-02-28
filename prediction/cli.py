#!/usr/bin/env python3
# coding=utf-8

import argparse
import logging

import numpy as np
from flask import Flask, request, jsonify
import flask
from prediction import Matching, flatten_list, skill_set, description, to_prob
from database import Database
import psycopg2
from dotenv import load_dotenv


load_dotenv()
database = Database()

resumes = database.get_resumes()
vacancies = database.get_vacancies()

corpus = [description(resume) + [skill_set(resume)] for resume in resumes]
corpus = flatten_list(corpus)

model = Matching()
model.fit(corpus)

relevance = []
for resume in resumes:
    for vacancy in vacancies:
        score = model.score_pair(resume, vacancy.get('description'))
        item = ([resume.get('id'), vacancy.get('id'), to_prob(score) / 100])
        relevance.append(item)
        # print('{}-{}'.format(resume.get('id'), vacancy.get('id')))

database.save_relevance(relevance)

