# A Conversational Agent for Mental Health

<img src ="https://github.com/user-attachments/assets/03cd2b7b-9f49-4012-9cfb-709895022f94" width=100%>
<img src ="https://github.com/user-attachments/assets/27cde778-4b15-4144-9884-0900fb6a70b9" width=100%>

## Intro

This repository contains my undergraduate Computer Science final year project at Swansea University. 

I explored how LLM-based systems can be used for mental health support, focusing on the trade-off between empathy, safety, transparency, privacy and conversational quality.

To investigate this, I developed and compared two systems:
  - A baseline pure LLM chatbot.
  - An enhanced chatbot with additional safety and therapeutic features. 

If you are interested in learning more about this project, please refer to the full dissertation below.

**Dissertation:** 📄 [A Conversational Agent for Mental Health (PDF)](AConversationalAgentForMentalHealth.pdf) (This dissertation was awarded a First Class grade)

## Features

The enhanced chatbot incorporated the following components:

- <b>Crisis Detection Layer:</b> Detects high-risk user input and overrides normal flow to highlight emergency support resources to the user.
- <b>Emotion Classification:</b> Uses a ML language classifier to identify the underlying emotion in users' inputs to improve response quality.
- <b>CBT-Based Response Strategy Selection:</b> Applies CBT-based strategies in the chatbot's responses depending on the detected emotion.
- <b>Prompt Guidance:</b> Every prompt sent to the LLM is provided with strict behavioural constraints.
- <b>Transparency Notice:</b> Communicates system limitations, privacy constraints, and the chatbot's intended functionality to the user.

## System Architecture

<img src ="https://github.com/user-attachments/assets/09113b32-82f2-4df3-96c9-382821532fd9" width=100%>
<p align="center"><i>Flowchart of the enhanced model depicting the data pipeline between user and chatbot.</i></p>
<img src ="https://github.com/user-attachments/assets/c6063bb7-db41-4f8c-bc74-7984772f0bf4" width=100%>
<p align="center"><i>Flowchart of CBT strategy selection process.</i></p>

Full technical details are available in the dissertation.

## Findings

An evaluation consisting of user study and system testing revealed several trade-offs in AI-based mental health systems:

- Transparency increased perceived honesty but did not directly improve user trust.
- Structured CBT responses improved therapeutic effectiveness but sometimes reduced conversational quality.
- There is a clear tension between ethical transparency and users prefering more human-like interactions with the chatbot.

Overall, the results suggest that hybrid architectures combining generative and rule-based systems are necessary for safe deployment of mental health conversational agents.

Full evaluation details and results are available in the dissertation.

## Demo

Watch a full showcase of the working project in this <a href="https://youtu.be/3J3fwARlao0">youtube video!</a> 

## Set Up

To access the project and explore it yourself please follow this set up.

### Requirements

- Laravel 10+
- Docker
- Artisan
- Modern web browser

### Steps

- Clone this repository.
- Install all PHP and Node dependencies needed for a Laravel project.
- Create a .env file and your 'Hugging Face' token to the .env file.
- Start Laravel Sail.
- Open the application in your browser:
  - Enhanced version: http://localhost/chatA
  - Baseline version: http://localhost/chatB
