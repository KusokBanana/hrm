#!/usr/bin/env python3
# coding: utf-8

# In[3]:

import pandas as pd
import numpy as np
import json
from datetime import datetime
from sklearn.feature_extraction.text import CountVectorizer, TfidfVectorizer
from scipy.sparse import csr_matrix
from pymystem3 import Mystem
# In[2]:


def skill_set(resume):
    res = resume['skill_set']
    return ' '.join(res)

def description(resume):
    fact = resume['experience']
    res = [i['description'] for i in fact]
    if res == []:
        res = ['empty_desc']
    return res

def delta_days_skill(resume):
    date_format = "%Y-%m-%d"
    fact = resume['experience']
    res = []
    for skill in fact:
        a = datetime.strptime(skill['start'], date_format)
        if skill['end'] == None:
            b = datetime.today()
        else:
            b = datetime.strptime(skill['end'], date_format)
        delta = b - a
        res.append(max(delta.days, 1))

    if len(fact) == 0:
        return [1]
    else:
        return res
    
def preproc_resume(resume):
    return description(resume), delta_days_skill(resume), skill_set(resume)

def flatten_list(lists):
    lf = []
    for l in lists:
        lf += l
    return lf

mystem = Mystem()
def lemmatize(text):
    text = ''.join(mystem.lemmatize(text)).strip()
    return text

def lemmatize_list(lst):
    return list(map(lemmatize, lst))

def to_prob(x, k=7):
    return 100 - 100 * np.exp(-k * x)


# In[3]:


class Matching:
    
    def fit(self, corpus, min_df = 1, max_df = 1.0):
        self.vectorizer = TfidfVectorizer(min_df=min_df,max_df = max_df)
        self.vectorizer.fit(lemmatize_list(corpus))
        
    def score_pair(self, resume, vac, skill_weight=1):
        '''
        Рассчитывает скор для пары резюме-вакансия
        Резюме в формате словаря, полученного из json
        Вакансия = str
        skill_weight - вес вектора скилов в расчете скора
        '''
        descriptions, experiences, skills = preproc_resume(resume)
        descriptions_features = self.vectorizer.transform(lemmatize_list(descriptions))
        skills_features = self.vectorizer.transform(lemmatize_list([skills]))
        vac_features = self.vectorizer.transform(lemmatize_list([vac]))
        w_descriptions_features = descriptions_features.multiply(np.array(experiences).reshape((-1,1))) / sum(experiences)
        w_descriptions_features = csr_matrix(w_descriptions_features.sum(axis=0))
        
        return w_descriptions_features.multiply(vac_features).sum() + skills_features.multiply(vac_features).sum() * skill_weight

    def score_vacancies(self, resume, vac_list, skill_weight=1):
        '''
        Рассчитывает скор для резюме и списка вакансий
        Резюме в формате словаря, полученного из json
        Вакансии = список, содержащий str
        '''
        descriptions, experiences, skills = preproc_resume(resume)
        descriptions_features = self.vectorizer.transform(lemmatize_list(descriptions))
        skills_features = self.vectorizer.transform(lemmatize_list([skills]))
        vac_features = self.vectorizer.transform(lemmatize_list(vac_list))
        w_descriptions_features = descriptions_features.multiply(np.array(experiences).reshape((-1,1))) / sum(experiences)
        w_descriptions_features = csr_matrix(w_descriptions_features.sum(axis=0))
        
        res = []
        for vac in vac_features:
            res.append(w_descriptions_features.multiply(vac).sum() + skills_features.multiply(vac).sum() * skill_weight)
        return res
    
    def score_resume(self, resume_list, vac, skill_weight=1):
        '''
        Рассчитывает скор для списка резюме и вакансии
        Резюме в формате списка словарей, полученных из json
        Вакансия = str
        '''
        vac_features = self.vectorizer.transform(lemmatize_list([vac]))
        
        res = []
        for resume in resume_list:
            descriptions, experiences, skills = preproc_resume(resume)
            descriptions_features = self.vectorizer.transform(lemmatize_list(descriptions))
            skills_features = self.vectorizer.transform(lemmatize_list([skills]))
            w_descriptions_features = descriptions_features.multiply(np.array(experiences).reshape((-1,1))) / sum(experiences)
            w_descriptions_features = csr_matrix(w_descriptions_features.sum(axis=0))
            res.append(w_descriptions_features.multiply(vac_features).sum() + skills_features.multiply(vac_features).sum() * skill_weight)
        return res


# In[1]:

# resumes = json.load(open('data_file_it/data_file_it.json'))
# vacancies = pd.read_csv('vacancy_all_it/vacancy_all_it.csv')
# vacancies = list(vacancies.iloc[:,1])


# In[5]:


'''
Обучение модели и единичный инференс
Модель обучается по корпусу текста из описаний опыта, скилов и вакансий
Инференс можно делать по данным, отсустствующим в обучающей выборке
'''

# corpus = [description(resume) + [skill_set(resume)] for resume in resumes]
# corpus = flatten_list(corpus)

# model = Matching()
# model.fit(corpus)
# model.score_pair(resumes[1], vacancies[1])


# # In[6]:


# # скоринг всех резюме по одной вакансии

# scores = model.score_resume(resumes, vacancies[1])
# np.array(scores)[:10]


# # In[7]:


# # скоринг всех вакансий по одному резюме

# scores = model.score_vacancies(resumes[1], vacancies)
# np.array(scores)[:10]


# In[ ]:
