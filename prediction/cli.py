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
for vacancy in vacancies:
    scores = model.score_resume(resumes, vacancy.get('description'))
    for i in range(len(scores)):
        score = scores[i]
        resume = resumes[i]
        item = ([resume.get('id'), vacancy.get('id'), to_prob(score) / 100])
        relevance.append(item)

database.save_relevance(relevance)

