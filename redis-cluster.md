#### 安装ruby
* 安装一些依赖
```
yum -y install make gcc openssl-devel zlib-devel gcc gcc-c++ make autoconf readline-devel curl-devel expat-devel gettext-devel ncurses-devel sqlite3-devel mysql-devel httpd-devel wget which
```

```
[root@VM_0_12_centos ~]# wget https://cache.ruby-lang.org/pub/ruby/2.3/ruby-2.3.1.tar.gz

tar xvf ruby-2.3.1.tar.gz 
cd ruby-2.3.1
./configure -prefix=/usr/local/ruby
make
make install

安装完成 并且加入环境变量
[root@VM_0_12_centos ~]# ruby --version
ruby 2.3.1p112 (2016-04-26 revision 54768) [x86_64-linux]

安装redis-4.0.0.gem
wget http://rubygems.org/downloads/redis-4.0.0.gem
/usr/local/ruby/bin/gem install -l  redis-4.0.0.gem 
/usr/local/ruby/bin/gem list -- check redis gem

安装
[root@VM_0_12_centos ~]# /usr/local/ruby/bin/gem install -l  redis-4.0.0.gem 
Successfully installed redis-4.0.0
Parsing documentation for redis-4.0.0
Installing ri documentation for redis-4.0.0
Done installing documentation for redis after 0 seconds
1 gem installed

安装成功之后
[root@VM_0_12_centos ~]# /usr/local/ruby/bin/gem list -- check redis gem

*** LOCAL GEMS ***

bigdecimal (1.2.8)
did_you_mean (1.0.0)
io-console (0.4.5)
json (1.8.3)
minitest (5.8.3)
net-telnet (0.1.1)
power_assert (0.2.6)
psych (2.0.17)
rake (10.4.2)
rdoc (4.2.1)
redis (4.0.0, 3.3.0)
test-unit (3.1.5)
```
* 安装完成之后 进入redis 对应版本的解压目录 执行./redis-trib.rb 
```
[root@VM_0_12_centos src]# pwd
/root/redis-4.0.10/src
[root@VM_0_12_centos src]# ./redis-trib.rb 
Usage: redis-trib <command> <options> <arguments ...>

  create          host1:port1 ... hostN:portN
                  --replicas <arg>
  check           host:port
  info            host:port
  fix             host:port
                  --timeout <arg>
  reshard         host:port
                  --from <arg>
                  --to <arg>
                  --slots <arg>
                  --yes
                  --timeout <arg>
                  --pipeline <arg>
  rebalance       host:port
                  --weight <arg>
                  --auto-weights
                  --use-empty-masters
                  --timeout <arg>
                  --simulate
                  --pipeline <arg>
                  --threshold <arg>
  add-node        new_host:new_port existing_host:existing_port
                  --slave
                  --master-id <arg>
  del-node        host:port node_id
  set-timeout     host:port milliseconds
  call            host:port command arg arg .. arg
  import          host:port
                  --from <arg>
                  --copy
                  --replace
  help            (show this help)

For check, fix, reshard, del-node, set-timeout you can specify the host and port of any working node in the cluster.
[root@VM_0_12_centos src]# 
```
* 删除所有进程
```
[root@VM_0_12_centos ~]# ps aux|grep redis-server |grep 700 | awk '{print $2}'
13834
14088
14099
14106
14114
14141
[root@VM_0_12_centos ~]# ps aux|grep redis-server |grep 700 | awk '{print $2}'| xargs kill
```
* 启动7000 - 7005 redis 服务
```
[root@VM_0_12_centos conf]# ps aux|grep redis 
root     28081  0.0  0.4 145304  7588 ?        Ssl  22:24   0:00 redis-server *:7000 [cluster]
root     28086  0.0  0.1 145304  2656 ?        Ssl  22:24   0:00 redis-server *:7001 [cluster]
root     28096  0.0  0.1 145304  2660 ?        Ssl  22:24   0:00 redis-server *:7002 [cluster]
root     28106  0.0  0.1 145304  2656 ?        Ssl  22:24   0:00 redis-server *:7003 [cluster]
root     28132  0.0  0.1 145304  2668 ?        Ssl  22:25   0:00 redis-server *:7004 [cluster]
root     28140  0.0  0.1 145304  2656 ?        Ssl  22:25   0:00 redis-server *:7005 [cluster]
root     28170  0.0  0.0 112704   976 pts/0    R+   22:25   0:00 grep --color=auto redis
[root@VM_0_12_centos conf]# 

```
*redis-trib.rb 启动集群
```
// --replicas 1 代表1主1从 
 redis-trib.rb  create --replicas 1 127.0.0.1:7000 127.0.0.1:7001 127.0.0.1:7002 127.0.0.1:7003 127.0.0.1:7004 127.0.0.1:7005
```
* 执行上边命令之后的结果
```
>>> Creating cluster
>>> Performing hash slots allocation on 6 nodes...
Using 3 masters: // 选取了3个主节点
127.0.0.1:7000
127.0.0.1:7001
127.0.0.1:7002
Adding replica 127.0.0.1:7004 to 127.0.0.1:7000  //分配从节点
Adding replica 127.0.0.1:7005 to 127.0.0.1:7001
Adding replica 127.0.0.1:7003 to 127.0.0.1:7002
>>> Trying to optimize slaves allocation for anti-affinity
[WARNING] Some slaves are in the same host as their master
M: b9e5d1228ebd295b0faf4d82c1ebf791fb0b602f 127.0.0.1:7000
   slots:0-5460 (5461 slots) master  //分配主从的槽
M: b6fb6949303036e084a2148a23158890d76ce978 127.0.0.1:7001
   slots:5461-10922 (5462 slots) master
M: 10aab594654fec513ef34c7724e0b3e87402017b 127.0.0.1:7002
   slots:10923-16383 (5461 slots) master
S: 3ce36f6ad65c7f18861bc54dceed901904533568 127.0.0.1:7003
   replicates b9e5d1228ebd295b0faf4d82c1ebf791fb0b602f
S: 198146ca6458765b0547184b594336d1994fd6aa 127.0.0.1:7004
   replicates b6fb6949303036e084a2148a23158890d76ce978
S: e622588f8b7088dd3786131d934b63b37937c9e2 127.0.0.1:7005
   replicates 10aab594654fec513ef34c7724e0b3e87402017b
Can I set the above configuration? (type 'yes' to accept): yes
>>> Nodes configuration updated
>>> Assign a different config epoch to each node
>>> Sending CLUSTER MEET messages to join the cluster
Waiting for the cluster to join...
>>> Performing Cluster Check (using node 127.0.0.1:7000)
M: b9e5d1228ebd295b0faf4d82c1ebf791fb0b602f 127.0.0.1:7000
   slots:0-5460 (5461 slots) master
   1 additional replica(s)
M: 10aab594654fec513ef34c7724e0b3e87402017b 127.0.0.1:7002
   slots:10923-16383 (5461 slots) master
   1 additional replica(s)
S: e622588f8b7088dd3786131d934b63b37937c9e2 127.0.0.1:7005
   slots: (0 slots) slave
   replicates 10aab594654fec513ef34c7724e0b3e87402017b
M: b6fb6949303036e084a2148a23158890d76ce978 127.0.0.1:7001
   slots:5461-10922 (5462 slots) master
   1 additional replica(s)
S: 3ce36f6ad65c7f18861bc54dceed901904533568 127.0.0.1:7003
   slots: (0 slots) slave
   replicates b9e5d1228ebd295b0faf4d82c1ebf791fb0b602f
S: 198146ca6458765b0547184b594336d1994fd6aa 127.0.0.1:7004
   slots: (0 slots) slave
   replicates b6fb6949303036e084a2148a23158890d76ce978
[OK] All nodes agree about slots configuration.
>>> Check for open slots...
>>> Check slots coverage...
[OK] All 16384 slots covered.
```
* 搭建成功
```
[root@VM_0_12_centos conf]# redis-cli -p 7000 cluster nodes
10aab594654fec513ef34c7724e0b3e87402017b 127.0.0.1:7002@17002 master - 0 1545057840994 3 connected 10923-16383
e622588f8b7088dd3786131d934b63b37937c9e2 127.0.0.1:7005@17005 slave 10aab594654fec513ef34c7724e0b3e87402017b 0 1545057839000 6 connected
b9e5d1228ebd295b0faf4d82c1ebf791fb0b602f 127.0.0.1:7000@17000 myself,master - 0 1545057837000 1 connected 0-5460
b6fb6949303036e084a2148a23158890d76ce978 127.0.0.1:7001@17001 master - 0 1545057840000 2 connected 5461-10922
3ce36f6ad65c7f18861bc54dceed901904533568 127.0.0.1:7003@17003 slave b9e5d1228ebd295b0faf4d82c1ebf791fb0b602f 0 1545057839000 4 connected
198146ca6458765b0547184b594336d1994fd6aa 127.0.0.1:7004@17004 slave b6fb6949303036e084a2148a23158890d76ce978 0 1545057838000 5 connected

[root@VM_0_12_centos conf]# redis-cli -p 7000 cluster slots
1) 1) (integer) 10923
   2) (integer) 16383
   3) 1) "127.0.0.1"
      2) (integer) 7002
      3) "10aab594654fec513ef34c7724e0b3e87402017b"
   4) 1) "127.0.0.1"
      2) (integer) 7005
      3) "e622588f8b7088dd3786131d934b63b37937c9e2"
2) 1) (integer) 0
   2) (integer) 5460
   3) 1) "127.0.0.1"
      2) (integer) 7000
      3) "b9e5d1228ebd295b0faf4d82c1ebf791fb0b602f"
   4) 1) "127.0.0.1"
      2) (integer) 7003
      3) "3ce36f6ad65c7f18861bc54dceed901904533568"
3) 1) (integer) 5461
   2) (integer) 10922
   3) 1) "127.0.0.1"
      2) (integer) 7001
      3) "b6fb6949303036e084a2148a23158890d76ce978"
   4) 1) "127.0.0.1"
      2) (integer) 7004
      3) "198146ca6458765b0547184b594336d1994fd6aa"
      
[root@VM_0_12_centos conf]# redis-cli -p 7000 cluster info
cluster_state:ok
cluster_slots_assigned:16384
cluster_slots_ok:16384
cluster_slots_pfail:0
cluster_slots_fail:0
cluster_known_nodes:6
cluster_size:3   //master 节点
cluster_current_epoch:6
cluster_my_epoch:1
cluster_stats_messages_ping_sent:302
cluster_stats_messages_pong_sent:279
cluster_stats_messages_sent:581
cluster_stats_messages_ping_received:274
cluster_stats_messages_pong_received:302
cluster_stats_messages_meet_received:5
cluster_stats_messages_received:581
[root@VM_0_12_centos conf]#       
```
