#!/usr/bin/env python3
# coding=utf-8

import os
import argparse
import logging
from flask import Flask, request, jsonify
import flask


app = Flask(__name__)

@app.route('/')
def handler():
    return 'Hello world'
    # return generate(request.args.get('prompt'))

if __name__ == "__main__":
    # Only for debugging while developing
    app.run(host='0.0.0.0', debug=True, port=80)