FROM tiangolo/uwsgi-nginx-flask:python3.8

WORKDIR /usr/src/app
EXPOSE 80

COPY prediction .
RUN pip3 install -r requirements.txt

ENTRYPOINT ["python3", "app.py"]
