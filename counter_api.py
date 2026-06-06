from flask import Flask, request, jsonify
import json
import os

app = Flask(__name__)
DB_FILE = "/var/www/html/unique_visitors.json"

def load_visitors():
    if os.path.exists(DB_FILE):
        with open(DB_FILE, "r") as f:
            try:
                return json.load(f)
            except:
                return []
    return []

def save_visitors(data):
    with open(DB_FILE, "w") as f:
        json.dump(data, f)

@app.route('/api/visit', methods=['GET'])
def process_visit():
    # Grab real IP from behind Nginx proxy
    ip = request.headers.get('X-Forwarded-For', request.remote_addr)
    if ip and ',' in ip:
        ip = ip.split(',')[0].strip()

    visitors = load_visitors()
    
    # Store IP uniquely if it hasn't landed before
    if ip not in visitors:
        visitors.append(ip)
        save_visitors(visitors)
        
    return jsonify({"total_unique": len(visitors)})

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5001)
