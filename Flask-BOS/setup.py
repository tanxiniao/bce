#!/usr/bin/evn python

from setuptools import setup

setup(
    name='Flask-BOS',
    version='0.10.1',
    description='BOS for Flask',
    license='MIT',
    author='ZhangWen'
    author_email='tanxiniao@qq.com'
    py_modules=['flask_bos'],
    zip_safe=False,
    include_package_data=True,
    platforms='any',
    keywords='bos for flask',
    packages=['baidubce'],
    package_data={'': ['LICENSE']},
    install_requires=[
        'setuptools',
        'Flask'
    ],
    classifiers=[
        'Development Status :: 4 - Beta',
        'Environment :: Web Environment',
        'Intended Audience :: Developers',
        'License :: OSI Approved :: MIT License',
        'Operating System :: OS Independent',
        'Programming Language :: Python',
        'Topic :: Internet :: WWW/HTTP :: Dynamic Content',
        'Topic :: Software Development :: Libraries :: Python Modules'
    ]
)
