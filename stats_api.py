from flask import Flask, jsonify
from flask_cors import CORS
import psutil
import time

app = Flask(__name__)
CORS(app)  # This allows your HTML file to talk to this API

@app.route('/stats')
def get_stats():
    # Get CPU usage %
    cpu_percent = psutil.cpu_percent(interval=1)
    
    # Get Memory usage
    mem = psutil.virtual_memory()
    mem_used_gb = round(mem.used / (1024**3), 2)
    mem_total_gb = round(mem.total / (1024**3), 2)
    
    # Get Uptime (since boot)
    uptime_seconds = time.time() - psutil.boot_time()
    days = int(uptime_seconds // 86400)
    hours = int((uptime_seconds % 86400) // 3600)
    minutes = int((uptime_seconds % 3600) // 60)
    
    uptime_str = f"{days}d {hours}h {minutes}m"

    return jsonify({
        "cpu": cpu_percent,
        "mem_used": mem_used_gb,
        "mem_total": mem_total_gb,
        "mem_percent": mem.percent,
        "uptime": uptime_str
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
