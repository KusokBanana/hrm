FROM tiangolo/uwsgi-nginx-flask:python3.8

#RUN pip3 install torch==1.7.0+cpu torchvision==0.8.1+cpu torchaudio==0.7.0 -f https://download.pytorch.org/whl/torch_stable.html

WORKDIR /usr/src/app
EXPOSE 80

#COPY requirements.txt ./
COPY prediction .
RUN pip3 install -r requirements.txt    # --no-cache-dir -r requirements.txt


#CMD ["gunicorn", "--workers=2", "--bind=0.0.0.0:80", "app:app"]
ENTRYPOINT ["python3", "app.py"]
