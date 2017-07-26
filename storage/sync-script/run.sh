#!/bin/sh

clear; php run.php |& tee -a output_$(date +%Y%m%d-%H%M%S).log
